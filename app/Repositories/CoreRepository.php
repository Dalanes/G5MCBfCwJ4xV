<?php

    namespace App\Repositories;

    use Illuminate\Database\Eloquent\Model;
    /**
     * Class CoreRepository
     *
     * @package App\Repositories
     *
     * Репозиторий работы с сущностью.
     * Может выдавать наборы данных.
     * не может создавать/изменять сущности
     */

    abstract class CoreRepository
    {
        /**
         * @var Model
         */

        protected $model;

        /**
         * CoreRepository constructor.
         */

        public function __construct()
        {
            $this->model = app($this->getModelClass());
        }

        /**
         * @return mixed
         */

        abstract protected function getModelClass();

        /**
         * @return Model|\Illuminate\Foundation\Application|mixed
         */

        protected function startCondition()
        {
            return clone $this->model;
        }
    }

?>
