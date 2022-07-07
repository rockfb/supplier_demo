<?php

namespace app\models\searchs;

use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;
use app\models\Supplier;

/**
 * SupplierSearch represents the model behind the search form of `app\models\Supplier`.
 */
class SupplierSearch extends Supplier
{
    protected $supportOpers = ['<', '<=', '>', '>=', '!='];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
//            [['id'], 'integer'],
            [['id', 'name', 'code', 't_status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $extra = ['pagination' => true])
    {
        $query = Supplier::find();

        // add conditions that should always apply here

        if ($extra['pagination']) {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Yii::$app->params['pagination']['pageSize'],
                ],
            ]);
        } else {
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => false,
            ]);
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query = $this->idFilter($query);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 't_status', $this->t_status]);

        return $dataProvider;
    }

    protected function idFilter(ActiveQuery $query)
    {
        $pattern = implode('|', $this->supportOpers);
        if (preg_match('/^(' . $pattern . ')([1-9]\d*)$/', $this->id, $match)) {
            $query->andFilterWhere([$match[1], 'id', $match[2]]);
        } else {
            $query->andFilterWhere([
                'id' => $this->id,
            ]);
        }
        return $query;
    }
}
