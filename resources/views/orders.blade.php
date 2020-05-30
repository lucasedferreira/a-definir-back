<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>

        @foreach($orders as $order)

            Nome: {{$order->name}}
            <br>
            Endereço: {{$order->address}}
            <hr>
            {{\Model\OrderItem::all()}}

        @endforeach

    </body>
</html>
