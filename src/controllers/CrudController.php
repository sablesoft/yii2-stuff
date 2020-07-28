<?php declare(strict_types=1);

namespace sablesoft\stuff\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\db\StaleObjectException;
use yii\base\InvalidRouteException;
use sablesoft\vue\VueManager;
use sablesoft\stuff\interfaces\SearchInterface;

/**
 * CrudController implements the CRUD actions for all CRUD controllers.
 *
 * @property-read array $vueConfig
 */
class CrudController extends Controller {

    protected string $modelsPath = 'app\models\\';
    protected string $searchModelsPath = 'app\models\search\\';
    protected ?string $modelClass = null;
    protected ?string $searchModelClass = null;
    protected array $primaryKey = ['id'];

    /** @var ActiveRecord|null $model */
    protected ?ActiveRecord $model = null;

    /**
     * @var array
     */
    protected array $_vueConfig = [];

    /**
     * @param string|null $path
     * @return array
     */
    public function getVueConfig(string $path = null) : array
    {
        return $path ?
            ArrayHelper::getValue($this->_vueConfig, $path) :
            $this->_vueConfig;
    }

    /**
     * @param string|null $path
     * @param mixed $config
     * @return $this
     */
    public function setVueConfig($config, string $path = null) : self
    {
        ArrayHelper::setValue($this->_vueConfig, $path, $config);
        return $this;
    }

    /**
     * @param array $config
     * @param string|null $path
     * @return $this
     */
    public function addVueConfig(array $config, string $path = null) : self
    {
        $oldConfig = (array) $this->getVueConfig($path);
        $newConfig = ArrayHelper::merge($oldConfig, $config);
        $this->setVueConfig($newConfig, $path);
        return $this;
    }

    /**
     * @param string $modelKey
     * @param array $where
     * @return array
     */
    public function gridParams(string $modelKey, array $where) : array
    {
        $class = $this->searchModelsPath . ucfirst($modelKey) . 'Search';
        /** @var SearchInterface|ActiveRecord $search */
        $search = new $class();
        $search->setAttributes($where);
        $provider = $search->search(\Yii::$app->request->queryParams);
        return [
            "${modelKey}Search"      => $search,
            "${modelKey}Provider"    => $provider
        ];
    }

    /**
     * @param ActiveRecord $model
     */
    public function setModel(?ActiveRecord $model) {
        $this->model = $model;
    }

    /**
     * @return ActiveRecord
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST']
                ]
            ]
        ];
    }

    /**
     * @param string $id
     * @param array $params
     * @return mixed
     * @throws InvalidRouteException
     */
    public function runAction($id, $params = []) {
        if ($pk = $this->primaryKey($params)) {
            $this->setModel($this->findModel($pk));
        }

        return parent::runAction($id, $params);
    }

    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        $this->applyVue($view, $params);
        return parent::render($view, $params);
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex() {
        $class = $this->getModelClass(true);
        /** @var ActiveRecord|SearchInterface $searchModel */
        $searchModel = new $class();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single model.
     * @return mixed
     */
    public function actionView() {
        if(!$model = $this->getModel()) {
            return $this->redirect('index');
        }
        $model->load(Yii::$app->request->get());

        return $this->render('view', [
            'model' => $this->getModel()
        ]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = $this->createModel();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $modelName = ucfirst($this->getUniqueId());
            Yii::$app->session->addFlash('success', "$modelName created!");
            if ($this->isAjax()) {
                return $this->asJson([
                    'success' => true,
                    'model' => $model->getAttributes()
                ]);
            } else {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        if ($this->isAjax()) {
            return $this->asJson([
                'success' => false,
                'errors' => $model->getErrors()
            ]);
        } else {
            return $this->render('create', [
                'model' => $model
            ]);
        }
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate() {
        if (!$this->getModel()) {
            return $this->redirect('index');
        }

        if ($this->getModel()->load(Yii::$app->request->post())) {
            if ($this->getModel()->save()) {
                $name = ucfirst($this->getUniqueId());
                Yii::$app->session->addFlash('success', Yii::t('app', "$name successfully updated!"));
                return $this->redirect(['view', 'id' => $this->getModel()->id]);
            } else {
                foreach($this->getModel()->getErrors() as $error)
                    Yii::$app->session->addFlash('error', reset($error));
            }
        }

        return $this->render('update', [
            'model' => $this->getModel()
        ]);
    }

    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete() {
        if (!$this->getModel()) {
            Yii::$app->session->addFlash('error', 'Model for deleting not founded!');
            return $this->goReferrer();
        }

        try {
            if (!$this->getModel()->delete()) {
                foreach ((array) $this->getModel()->getErrors() as $attribute => $errors) {
                    foreach ($errors as $error) {
                        Yii::$app->session->addFlash('error', ucfirst($attribute) . ': ' . $error);
                    }
                }
            } else {
                $modelName = ucfirst($this->getUniqueId());
                Yii::$app->session->addFlash('success', Yii::t('app', "$modelName deleted!"));
            }
        } catch (StaleObjectException $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::$app->session->addFlash('error', $e->getMessage());
        }

        return $this->goReferrer();
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param array $pk
     * @return ActiveRecord|null the loaded model
     */
    protected function findModel(array $pk) {
        /** @var ActiveRecord $class */
        $class = $this->getModelClass();
        if (($model = $class::findOne($pk)) !== null) {
            return $model;
        }

        $error = \Yii::t('yii', 'The requested page does not exist.');
        Yii::$app->session->addFlash('error', $error );

        return null;
    }

    /**
     * @param bool $isSearch
     * @return string
     */
    protected function getModelClass(bool $isSearch = false) : string
    {
        $class = $isSearch ? $this->modelClass : $this->searchModelClass;
        if ($class) {
            return $class;
        }

        $name = get_class($this);
        $parts = explode("\\", $name);
        $name = end($parts);
        $name = preg_replace('/Controller/', '', $name);
        return $isSearch ?
            $this->searchModelsPath . "${name}Search" :
            $this->modelsPath . $name;
    }

    /**
     * @param bool $useCache
     * @param bool $searchModel
     * @return ActiveRecord
     */
    protected function createModel(bool $useCache = true, bool $searchModel = false) : ActiveRecord
    {
        if ($useCache && $this->getModel()) {
            return $this->getModel();
        }
        $class = $this->getModelClass($searchModel);
        $model = new $class();
        $this->setModel($model);

        return $model;
    }

    /**
     * @param array $params
     * @return array|null
     */
    protected function primaryKey(array $params) : ?array
    {
        $pk = [];
        foreach ($this->primaryKey as $key) {
            if (empty($params[$key])) {
                return null;
            }
            $pk[$key] = $params[$key];
        }

        return $pk;
    }

    /**
     * @return Response
     */
    protected function goReferrer() : Response
    {
        return $this->redirect(\Yii::$app->request->referrer ?: \Yii::$app->homeUrl);
    }

    /**
     * @param string $view
     * @param array $params
     */
    protected function applyVue(string $view, array $params) : void
    {
        $config = $this->getVueConfig();
        if (!array_key_exists($view, $config)) {
            return;
        }

        $config = $config[$view];
        $data = $config['data'] ?? [];
        $data['area'] = $this->getUniqueId();
        foreach ($params as $param) {
            if (!is_object($param)) {
                continue;
            }
            if (method_exists($param, 'getModelArea') &&
                method_exists($param, 'safeAttributes')) {
                $attributes = [];
                foreach($param->safeAttributes() as $attribute) {
                    $attributes[$attribute] = $param->$attribute;
                }
                $data = ArrayHelper::merge($data, [
                    $param->getModelArea() => $attributes
                ]);
            }
        }
        $config['data'] = $data;
        /** @var VueManager $manager */
        /** @noinspection PhpUndefinedFieldInspection */
        $manager = \Yii::$app->vueManager;
        $manager->register($config);
    }

    /**
     * @param $model
     * @param string $attribute
     * @param $value
     */
    protected function updateAttribute(ActiveRecord $model, string $attribute, $value)
    {
        $model->$attribute = $value;
        $model->updateAttributes([
            $attribute => $value,
        ]);
        $model->afterSave(false, [
            $attribute  => $value,
        ]);
        $class = get_class($model);
        $parts = explode('\\', $class);
        $name = end($parts);
        \Yii::$app->session->setFlash(
            'success',
            \Yii::t('app', "$name $attribute successfully set as $value!")
        );
    }

    /**
     * @return bool
     */
    protected function isAjax() : bool
    {
        return Yii::$app->request->isAjax;
    }
}
