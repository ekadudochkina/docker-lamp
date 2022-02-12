/**
 * Контроллер для магазина Home-Studio
 * 
 * @constructor
 * @returns {HsCartController}
 */
function HsCartController()
{
    var cartModal = null;
    var cart = null;
    var translations = {"checkoutText": "checkout"};
    var counters = {};


    this.start = function ()
    {
        cart.load();
        console.log("Initializing shop", cart);

        //adding cart modal
        var template = this.getCartModalHtml();
        $(document.body).prepend(template);
        var el = $(".hs-cart").get(0);
        $(el).hide();
        cartModal = el;

        //processing cartModal
        $(".hs-cart-button").each(function () {

            $(this).on('click', function (e) {
                $(cartModal).fadeToggle();
                e.stopPropagation();
            }.bind(this));
        });
        //Скрываем при клике на документ
        $(document).on('click', function () {
            $(cartModal).fadeOut();
        }.bind(this));
        //Но не на само окно
        $(cartModal).on('click', function (e) {
            console.log('cart click ');
            e.stopPropagation();
        }.bind(this));
        //на крест тоже скрываем
        $(cartModal).find('.hs-cart-close ').on('mousedown ', function () {
            $(cartModal).fadeOut();
        }.bind(this));



        //adding add-to-cart-button
        var self = this;
        var timeOutForCartHide = null;
        $(".hs-cart-add").on("click", function (e) {
            var item = self.getItemForHtmlElement(this);
            var counter = self.getCounterForItem(item);
            item.setQuantity(counter.getValue());
            counter.setValue(counter.min);
            console.log("adding item: ", item);
            cart.addItem(item);
            $(cartModal).fadeIn(500);
            window.clearTimeout(timeOutForCartHide);
            timeOutForCartHide = window.setTimeout(function () {
                $(cartModal).fadeOut(500);
            }, 5000);
            e.stopPropagation();
        });

        this.detectItems();

        cart.on("refresh", function () {
            console.log("Cart refresh");

            var items = cart.getItems();
            //Отображаем цифры над корзиной
            var numberElement = $(".cart .number").get(0);
            if (!cart.hasItems())
            {
                $(numberElement).hide();
            } else
            {
                $(numberElement).show();
                $(numberElement).html(items.length);
            }

            //Сначала удаляем элементы, удаленных вещей
            //Это должно быть вначале, так как только тут проверка на присутствие вещи
            $(".hs-cart-item-element").each(function () {
                var id = self.getItemIdFromClass($(this).attr("class"));
                var cartItem = cart.getItemById(id);
                if (!cartItem)
                {
                    $(this).remove();
                }
            });


            //Отображаем товары в окне корзины
            var spaceElement = $(cartModal).find(".cart-items").get(0);
            $(spaceElement).html("");

            for (var i = 0; i < items.length; i++)
            {
                var item = items[i];
                //Отображаем один товар
                var html = "<div class='item'>\
                    <div class='image'>\
                        <img src='{{image}}'/>\
                    </div>\
                    <div class='text'>\
                        <div class='title'>{{title}}</div>\
                        <div class='data'>\
                            <span class='quantity'>{{quantity}}</span>\
                            <span class=''>x</span>\
                            <span class='price'>{{price}}</span>\
                        </div>\
                        <div class='additional'></div>\
                        <div class='delete'>\
                            <i class='fa fa-times'></i>\
                        </div>\
                    </div>\
                </div>";
                html = html.replace("{{title}}", item.getTitle());
                html = html.replace("{{image}}", item.getImage());
                html = html.replace("{{size}}", item.size);
                // var price = cart.formPriceString(item.getPrice());

                var price = item.price;

                html = html.replace("{{price}}", price);
                html = html.replace("{{quantity}}", item.getQuantity());
                var el = $(html);
                //console.log(el.find(".delete"));
                el.find('.delete').on("click", function (item) {
                    console.log(arguments);
                    cart.removeItem(item);
                }.bind(this, item));


                $(spaceElement).append(el);
                if (i !== items.length - 1)
                {
                    $(spaceElement).append("<div class='spacer'></div>");
                }
            }

            //Или пустоту, если их нет
            if (items.length === 0)
            {
                $(spaceElement).html("Cart is empty");
            }

            //Отображаем кол-во товаров и общую сумму
            var subtotal = cart.formPriceString(cart.getSubtotal());
            var count = cart.getItems().length;
            $(".hs-cart-item-number").html(count);
            $(".hs-cart .heading .number").html(count);
            $(".hs-cart-subtotal").html(subtotal);

            $(".hs-cart-item-total").each(function () {
                var id = self.getItemIdFromClass($(this).attr("class"));
                var cartItem = cart.getItemById(id);
                var price = cart.getPriceForOneItem(id, cartItem.quantity);
                $(this).html(price);
            });
        });
        cart.refresh();
    };

    this.getItemIdFromClass = function (cl)
    {
        console.log(cl);
        var parts = cl.split(" ");
        for (var i = 0; i < parts.length; i++)
        {
            var chunk = parts[i];
            if (chunk.indexOf("item-") === 0)
            {
                var id = chunk.replace("item-", "");
                return id;
            }
        }
        return null;
    };

    this.getCartModalHtml = function ()
    {
        var str = "<div class='hs-cart'>  <div class='hs-cart-close'><i class='fa fa-times '></i></div>  <div class='heading'>    <span>Your cart</span>     (<span class='number'>1</span>)  </div>  <div class='cart-items'> </div>  <div class='bottom'>    <div class='buttons'>      <a class= 'btn btn-success ' href='{{checkoutUrl}}'>{{checkoutText}}</a></div></div></div>";
        for (var field in translations)
        {
            str = str.replace("{{" + field + "}}", translations[field]);
        }
        return str;
    };
    this.getItemForHtmlElement = function (element)
    {
        var cl = $(element).attr("class");
        var id = this.getItemIdFromClass(cl);
        var item = cart.loadItemWithId(id);
        return item;
    }
    this.detectItems = function ()
    {
        var self = this;
        $(".hs-cart-item-quantity-input").each(function () {
            var item = self.getItemForHtmlElement(this);
            var counter = self.getCounterForItem(item);
            counter.setInput(this);
            console.log(this);
            if ($(this).hasClass("hs-cart-existing"))
            {
                var realItem = cart.getItemById(item.id);
                console.log(realItem);
                counter.setValue(realItem.quantity);
                counter.on("change", function (counter) {
                    console.log(this);
                    this.quantity = counter.value;
                    cart.refresh();
                }.bind(realItem, counter));
            }


        });

        $(".hs-cart-item-quantity-add").each(function () {
            var item = self.getItemForHtmlElement(this);
            var counter = self.getCounterForItem(item);
            counter.setPlusButton(this);
        });
        $(".hs-cart-item-quantity-remove").each(function () {
            var item = self.getItemForHtmlElement(this);
            var counter = self.getCounterForItem(item);
            counter.setMinusButton(this);
        });

        $(".hs-cart-item-remove").on("click", function (e) {
            var id = self.getItemIdFromClass($(this).attr("class"));
            var item = cart.getItemById(id);
            cart.removeItem(item);
            e.stopPropagation();
        });
    };

    /**
     * 
     * @param {HsCartItem} item
     * @returns {HsCounter}
     */
    this.getCounterForItem = function (item)
    {
        if (counters[item.id])
        {
            return counters[item.id];
        }
        var counter = new HsCounter();
        counter.setMinimum(1);
        counters[item.id] = counter;
        return counter;
    };

    this.init = function ()
    {
        cart = new HsCart();
        var serverTranslations = hs.getServerParam("hsCartTranslations");
        if (serverTranslations)
        {
            for (var name in serverTranslations)
            {
                translations[name] = serverTranslations[name];
            }
        }
    };
    this.getCart = function()
    {
        return cart;
    }
       
    this.init();
}