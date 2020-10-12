<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\FlightsRepository;

class FlightsController extends Controller
{
    private $flightsRepository;

    public function __construct()
    {
        $this->flightsRepository = app(FlightsRepository::class);
    }
    /**
     * Вывод всех рейсов
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $flights = $this->flightsRepository->getFlightsIndex();
        $quantityPages = $this->flightsRepository->getQuantityPages();

        return \Response::view("welcome", compact("flights", "quantityPages"));
    }

     /** Метод получения следующих данных
     *   о рейсах, учитывая пользовательские настройки:
      * @param Request $request:
      *  nextPage   - страница, которую необходимо отобразить
      *  sort       - поле, по которому производится сортировка
      *  filter     - настройки фильтра [nameFilter, condition, valueFilter]
      * @return array [
      *         @var array $nextFlights   - записи рейсов, которые получает юзер
      *         @var int    $quantityPages - количество страниц
      * ]
     */

    public function getNextPageFlights(Request $request)
    {

        $nextFlights = $this->flightsRepository->getFlights(
            $request->input("nextPage"),
            $request->input("sort"),
            $request->input("filter")
        );

        if (isset($request->input("filter")["value"])) {
            return [$nextFlights["filteredFlights"], $nextFlights["quantityPages"]];
        }

        $quantityPages = $this->flightsRepository->getQuantityPages();

        return [$nextFlights, $quantityPages];
    }

}
