<?php

use raelgc\view\Template;


$tpl = new Template(__DIR__ ."/orderStatus.html");

$tpl->order = $order;
$tpl->restaurant = $restaurant;

if($status == "APPROVED"){
    $tpl->block('BLOCK_CONFIRMED_ORDER');
}else if($status == "CANCELED"){
    $tpl->block('BLOCK_CANCELED_ORDER');
}else if($status == "SENT"){
    $tpl->block('BLOCK_SENT_ORDER');
}else if($status == "DONE"){
    $tpl->block('BLOCK_DONE_ORDER');
}else if($status == 'CREATED'){
    $tpl->block('BLOCK_CREATED_ORDER');
}

// formating strings

$tpl->totalNetValue = 'R$ ' . number_format($order->total_net_value, 2, ',', '.');
$tpl->deliveryFee  =  'R$ ' . number_format($order->delivery_fee, 2, ',', '.');
$tpl->orderDate    = date_format(date_create($order->created_at), "d/m/Y H:i");

if($order->delivery_type == 'delivery'){
    $tpl->block('BLOCK_TOTAL_DELIVERY_FEE');
    $tpl->block('BLOCK_DELIVERY_ADDRESS');

    if($restaurant->delivery_time_min > 0){
        $tpl->block('BLOCK_DELIVERY_TIME');
    }

}else{
    $tpl->block('BLOCK_TOTAL_TAKEOUT');

    if($restaurant->takeout_time > 0){
        $tpl->block('TAKEOUT_TIME');
    }
        
}

if($order->payment_method == 'creditCard'){

    $tpl->block('BLOCK_CARD_PAYMENT');

}else if($order->payment_method == "money"){

    $tpl->changeValue = $order['need_change'] ? 'Troco para R$ ' . number_format($order['change'], 2, ',', '.') : "Não Precisa de troco";
    $tpl->block('BLOCK_CASH_PAYMENT');

}

foreach($order->items as $item)
{
    
    $tpl->productName       = $item['menu_name'];
    $tpl->productQuantity   = $item['quantity'];
    $tpl->productPrice      = $item['menu_price'] > 0 && ($item['pizza_price_behavior'] == 'incremental' || $item['type'] == 'general') ? 'R$ ' .  number_format($item['quantity'] * $item['menu_price'], 2, ',', '.') : null;

    $subtotal = $item['item_sub_total'];

    if($item['pizza_price_behavior'] == 'incremental'){
        $tpl->pizzaPriceNotice = "";
    }else if($item['pizza_price_behavior'] == 'highest'){
        $tpl->pizzaPriceNotice = "Preço calculado pelo sabor de maior preço";
    }else if($item['pizza_price_behavior'] == 'average'){
        $tpl->pizzaPriceNotice = "Preço calculado pela média dos preços dos sabores";
    }else{
        $tpl->pizzaPriceNotice = "";
    }

    if($item['type'] == 'pizza'){

        if(isset($item['crusts'][0])){
            $tpl->crustName     = $item['crusts'][0]['name'];
            $tpl->crustPrice    = $item['crusts'][0]['price'] > 0 ? 'R$ ' . number_format($item['crusts'][0]['price'], 2, ',', '.') : null;
            $tpl->block("BLOCK_PIZZA_CRUST");

           // $subtotal += $item['crusts'][0]['price'];
        }

        foreach($item['flavors'] as $flavor){
            $tpl->pizzaFlavorName   = $flavor['name'];
            $tpl->pizzaFlavorPrice  = $flavor['price'] > 0 ? 'R$ ' . number_format($flavor['price'], 2, ',', '.') : null;
            $tpl->block("BLOCK_PIZZA_FLAVOR");

            if($item['pizza_price_behavior'] == 'incremental'){
               // $subtotal += $flavor['price'];
            }
        }

    }else if($item['type'] == 'general'){

        foreach($item['extras'] as $extra){
            $tpl->productExtraName = $extra['extra_name'];
            $tpl->productExtraQuantity = $extra['quantity'];
            $tpl->productExtraPrice = $extra['extra_price'] > 0 ?  'R$ ' . number_format($extra['extra_price'] * $extra['quantity'], 2, ',', '.') : null;
            $tpl->block("BLOCK_PRODUCT_EXTRA");

           // $subtotal += $extra['extra_price'] * $extra['quantity'];
        }

    }else if($item['type'] == 'combo'){

        foreach($item['comboItems'] as $comboItem)
        {

            $tpl->comboItemName = $comboItem['menu_name'];
            
            foreach($comboItem['flavors'] as $flavor)
            {
                $tpl->comboPizzaFlavorName = $flavor['name'];
                $tpl->comboPizzaFlavorPrice =  $flavor['price'] > 0 ?  'R$ ' . number_format($flavor['price'], 2, ',', '.') : null;
                
                if($comboItem['pizza_price_behavior'] == 'incremental'){
                    $tpl->comboPizzaPriceNotice = "";
                }else if($comboItem['pizza_price_behavior'] == 'highest'){
                    $tpl->comboPizzaPriceNotice = "Preço calculado pelo sabor de maior preço";
                }else if($comboItem['pizza_price_behavior'] == 'average'){
                    $tpl->comboPizzaPriceNotice = "Preço calculado pela média dos preços dos sabores";
                }

                $tpl->block('BLOCK_COMBO_PIZZA_FLAVOR');

                if($comboItem['pizza_price_behavior'] == 'incremental'){
                  //  $subtotal += $flavor['price'];
                }
            }

            $tpl->block('BLOCK_COMBO_ITEM');
        }

        foreach($item['extras'] as $extra){
            $tpl->productExtraName = $extra['extra_name'];
            $tpl->productExtraQuantity = $extra['quantity'];
            $tpl->productExtraPrice = $extra['extra_price'] > 0 ?  'R$ ' . number_format($extra['extra_price'] * $extra['quantity'], 2, ',', '.') : null;
            $tpl->block("BLOCK_COMBO_EXTRA");

           // $subtotal += $extra['extra_price'] * $extra['quantity'];
        }

    }

    $tpl->subtotal = 'R$ ' . number_format($subtotal , 2, ',', '.');
    
    
    if($item['type'] == 'combo'){
        $tpl->block('BLOCK_COMBO');
    }else{
        $tpl->block('BLOCK_SINGLE');
    }
    
    $tpl->block("BLOCK_ITEM_SUBTOTAL");
    
    $tpl->block('BLOCK_PRODUCT');
}

$tpl->show();
