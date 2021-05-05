<?php
session_start();

class ShopCart
{
	// глобальная переменная корзины
	public $cart;

	public function __construct() 
	{
		// записываем в глобальную переменную массив корзины из сессии
		$this->cart = $_SESSION['cart'];
	}

	// метод добавления товара в корзину
	public function addToCart($product_id, $price, $quantity, $name, $img) 
	{
		// проверяем есть ли такой товар в корзине
		if (!empty($this->cart[$product_id]))
		{
			// если есть добавляем количество
			$this->updateQuantity($product_id);
		} 
		else
		{
			// если нет, то добавляем товар в массив
			$this->cart[$product_id] = array(
				'quantity' => $quantity,
				'price' => $price,
				'name' => $name,
				'img' => $img
			);
		}		
		$_SESSION['cart'] = $this->cart;
		return true;
	}

	// метод обновления количество товара 
	public function updateQuantity($product_id) 
	{
		$this->cart[$product_id]['quantity'] = (int)$this->cart[$product_id]['quantity'] + 1;
		$_SESSION['cart'] = $this->cart;
		return true;
	}

	// метод прибавления количества товара
	public function plusProduct($product_id) 
	{
		return $this->updateQuantity($product_id);
	}

	// метод удаления количества товара
	public function minusProduct($product_id) 
	{
		$this->cart[$product_id]['quantity'] = (int)$this->cart[$product_id]['quantity'] - 1;
		// проверяем сколько товара в корзине осталось
		if ($this->cart[$product_id]['quantity'] == 0) {
			// если равно нулю, то удаляем товар из корзины
			unset($this->cart[$product_id]);
		}
		$_SESSION['cart'] = $this->cart;
		return true;
	}

	// метод удаления товара из корзины
	public function deleteFromCart($product_id) 
	{
		unset($this->cart[$product_id]);
		$_SESSION['cart'] = $this->cart;
		return true;
	}

	// метод подсчета общей стоимости товаров
	public function getTotalSumm() 
	{
		if ($this->cart) {			
			$total = 0;
			foreach ($this->cart as $key => $value) {
				$total += $value['price'] * $value['quantity'];
			} 
			return $total;
		} else {
			return '0';
		}
	}	

	// метод подсчета количества товаров в корзине
	public function getTotalProducts() 
	{
		return ($this->cart) ? count($this->cart) : '0' ;
	}

	// метод удаляем все товары из корзины
	public function clearCart() 
	{ 
		$this->cart = array();
		$_SESSION['cart'] = $this->cart;
		return true;
	}
 
 	// метод возвращения товара
 	public function getBasket()
 	{ 		
		$_SESSION['cart'] = $this->cart;
	    return $_SESSION['cart'];
 	}
}


$cart = new ShopCart;

if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $cart->addToCart($_POST['id'], $_POST['price'], '1', $_POST['name'], $_POST['img']);
    print_r(json_encode($cart->getBasket()));
}

if (isset($_POST['action']) && $_POST['action'] == 'plus') {
    $cart->plusProduct($_POST['id']); 
}

if (isset($_POST['action']) && $_POST['action'] == 'minus') {
    $cart->minusProduct($_POST['id']); 
}

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $cart->deleteFromCart($_POST['id']); 
}

if (isset($_POST['action']) && $_POST['action'] == 'getsumm') {
    print_r($cart->getTotalSumm()); 
}

if (isset($_POST['action']) && $_POST['action'] == 'getcount') {
    print_r($cart->getTotalProducts()); 
}

if (isset($_POST['action']) && $_POST['action'] == 'get') { 
    $basket = $cart->getBasket();
    $result = '';
    if ($basket) {
    foreach ($basket as $key => $value) {
        $result .= '<div class="item" data-id="'.$key.'">
                        <div class="item-img">
                            <img src="'.$value['img'].'">
                        </div>
                        <div class="item-title">
                            '.$value['name'].'
                            <p class="price">'.$value['price'].' руб</p>
                        </div>
						<div class="item-buttons">
							<div class="item-count">
								<button class="minus">-</button>
								<input type="num" readonly value="'.$value['quantity'].'">
								<button class="plus">+</button>
							</div>
							<div class="item-delete">
								<button class="delete">X</button>
							</div>
						</div>
                    </div>';
    }
    print_r($result);
    } else {
        print_r("Корзина пуста");
    }
}
 
if (isset($_POST['action']) && $_POST['action'] == 'send') {
    $name = (isset($_POST['name'])) ? 'Имя:<br><b>'. $_POST['name'] . '</b>' : '';
    $phone = (isset($_POST['phone'])) ? 'Телефон:<br><b>'. $_POST['phone']. '</b>' : '';
    $shop = (isset($_POST['delivery'])) ? 'Магазин:<br><b>'. $_POST['delivery']. '</b>' : '';
    $comments = (isset($_POST['comments'])) ? 'Комментарий к заказу:<br><b>'. $_POST['comments']. '</b>' : '';
    
    $mess = "
        <h2>Информация о покупателе</h2>
        $name<br>
        $phone<br>
        $shop<br>
        $comments<br>
        <br>
    ";
    

    $basket = $cart->getBasket();
    $result = '';
    foreach ($basket as $key => $value) {
        $result .= '<tr>
            <td><img width="100px" src="https://zerjg.ru/'.$value['img'].'"></td>
            <td>'.$value['name'].'</td>
            <td>Количество '.$value['quantity'].'</td>
        </tr>'; 
    }
    $mess .= '<table style="width:50%">'.$result.'</table>';
    
    $mess .= 'Общая стоимость заказа <b>' . $cart->getTotalSumm() . ' руб</b>';
    $mailheaders = "Content-type:text/html;charset=utf-8\r\n"; 
    // почтовый заголовок, указывает формат письма - текстовый и кодировку
    
    $mailheaders .= "From: Заказ с сайта <noreply@zerjg.ru>\r\n"; 
    // почтовый заголовок, указывает емайл отправителя
    $to = "lyalina-2020@mail.ru";
    $subject = "Заказ с сайта";
    
    mail($to, $subject, $mess, $mailheaders);
    $cart->clearCart();
    
}
