<?php
    namespace App\Repositories;

    use App\Models\Flights as Model;
    use Illuminate\Database\Eloquent\Collection;

    /**
     * Class FlightsRepository
     * @package App\Repositories
     */

    class FlightsRepository extends CoreRepository
    {
        /**
         * $countFlights - Количество рейсов
         * $quantityOfNotes - количество рейсов,
         * отображаемых на странице
         */

        private $countFlights;
        private $quantityOfNotes;
        /**
         * @var string[]
         */
        private $columns;

        public function __construct()
        {
            parent::__construct();
            $this->countFlights = $this->startCondition()->count();
            $this->quantityOfNotes = 10;
            $this->columns = [
                "name",
                "quantity",
                "distance",
                "date"
            ];

        }

        protected function getModelClass()
        {
            return Model::class;
        }

        public function getFlightsIndex()
        {
            $flights = $this->startCondition()
                ->select($this->columns)
                ->orderBy("name")
                ->offset(0)
                ->limit($this->quantityOfNotes)
                ->toBase()
                ->get()
                ->toArray();

            return $flights;
        }

        /**
         * Получение записей о рейсах
         * в зависимости от их количества,
         * выводимых на одной странице за раз,
         * и номера страницы
         *
         * @param int $numberOfPage - Номер страницы
         * @param string $sort  - поле, по которому будет
         * производиться сортировка [ name, quantity, distance]
         * @param object|array|null $filter - значения, по которому
         * будут фильтроваться данные [
         *                  name (сортируемое поле),
         *                  condition (условие, по которому производить выборку: less, equally, more, contain),
         *                  value (значение, по которому произвлоить) выборку
         *              ]
         * если присутствует фильтрация, то фильтруем данные,
         * вызывая метод filterOut()
         */

        public function getFlights($numberOfPage = 1, $sort = "name", $filter = null)
        {

            $flights = "";

            $beginVal   = $this->quantityOfNotes * ($numberOfPage - 1);

            if (isset($filter["value"])) {
                $flights = $this->filterOut($beginVal, $sort, $filter);
            } else {
                $flights = $this->startCondition()
                    ->select($this->columns)
                    ->orderBy($sort)
                    ->offset($beginVal)
                    ->limit($this->quantityOfNotes)
                    ->toBase()
                    ->get()
                    ->toArray();

            }

            return $flights;
        }

        /**
         * @param $beginVal - строка, после которой мы берём 10 значений
         * @param $sort     - поле, по которому происходит сортировка
         * @param $filter   - данные фильтра (настройки) :
         *      [nameFilter, - поле, по которому происходит фильтрация
         *       condition,  - условие ['less', 'equally', 'more', 'contains']
         *       value       - значение, по которому происходит выборка,
         *                     соответствуя условию
         * ]
         * @return array $filteredFlights - отфильтрованные и отсортрованные
         *                                  данные о рейсах
         */

        private function filterOut($beginVal, $sort, $filter)
        {

            $filteredFlights = [];

            switch ($filter["condition"]) {
                case "less":
                    $filteredFlights = $this->filter($beginVal, $sort,
                        [$filter["nameFilter"], "<", $filter["value"]]);

                    break;
                case "equally":
                    $filteredFlights = $this->filter($beginVal, $sort,
                        [$filter["nameFilter"],"=", $filter["value"]]);

                    break;
                case "more":
                    $filteredFlights = $this->filter($beginVal, $sort,
                        [$filter["nameFilter"], ">" ,$filter["value"]]);

                    break;
                case "contains":
                    $filteredFlights = $this->filter($beginVal, $sort,
                        [$filter["nameFilter"], "like", "%" . $filter["value"] . "%"]);

                    break;
            }

            return $filteredFlights;
        }

        /**
         * @param $beginVal
         * @param $sort
         * @param $where
         *
         * @return array $filtered[
         *  filteredFlights - отфильтрованные рейсы
         *  quantityPages   - количество страниц
         * ]
         *
         * производим выборку данных по переданным значениям
         */

        public function filter($beginVal, $sort, $where)
        {
            $quantityFlights = $this->startCondition()
                ->select($this->columns)
                ->where($where[0], $where[1], $where[2])
                ->count();

            $quantityPages = $this->getQuantityPages($quantityFlights);

            $filtredFlights = $this->startCondition()
                ->select($this->columns)
                ->where($where[0], $where[1], $where[2])
                ->orderBy($sort)
                ->offset($beginVal)
                ->limit($this->quantityOfNotes)
                ->toBase()
                ->get();

            $filtered = [
                "filteredFlights" => $filtredFlights,
                "quantityPages"  => $quantityPages
            ];

            return $filtered;

        }

        /**
         * Получение количества страниц, на которых располагаются записи о рейсах
         *
         * @param null|int  $quantityFlights - количество рейсов
         *                                     если параметр не передан , то данное значение === null,
         *                                     а количество страниц выдаётся исходя из общего количества
         *                                     рейсов
         *
         * @return false|float
         */

        public function getQuantityPages($quantityFlights = null)
        {
            if ($quantityFlights === null) {
                $quantityPages = $this->countFlights / $this->quantityOfNotes;
            } else {
                $quantityPages = $quantityFlights / $this->quantityOfNotes;
            }

            return ceil($quantityPages);
        }
    }
?>
