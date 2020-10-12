@extends("layouts.app")
@section("content")

    @php
        /**
        * $flights - массив, содержащий объеты с информацией
        * о поездках. поля {
        *       name, quantity, distance, date
        * }
        * $quantityPages - количество страниц
        */
    @endphp

    <div id="tableComponent">
        <table-component flights            = "{{json_encode($flights)}}"
                         route-to-get-page  = "{{route("getNextPage")}}"
                         quantity-pages     = "{{$quantityPages}}"
                         csrf-token         = "{{csrf_token()}}"
        >

        </table-component>
    </div>
@endsection
