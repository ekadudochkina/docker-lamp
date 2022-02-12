/**
 * Корзина для магазина
 * 
 * @constructor
 * @returns {HsCart}
 */
function HsCart()
{
    HsEventMixture.call(this);
    /**
     * @type {HsCartItem[]}
     */
    this.items = [];

    this.hasItems = function ()
    {
        var result = this.items.length !== 0;
        return result;
    }.bind(this);

    /**
     * Загружает товары из куки
     */
    this.load = function ()
    {
        var savedCookie = hs.getCookie("hsCartItems");
        try {
            if (savedCookie)
            {
                var savedItems = JSON.parse(savedCookie);
                for (var i = 0; i < savedItems.length; i++)
                {
                    var id = savedItems[i].i;
                    var quantity = savedItems[i].q;
                    var item = this.loadItemWithId(id);
                    item.setQuantity(quantity);
                    this.items.push(item);
                }

            }
            this.refresh();
        } catch (e)
        {
            console.log("Failed, resetting");
            this.items = [];
            this.save();
            this.refresh();
        }
    }
    

    this.loadItemWithId = function (id)
    {
        var items = hs.getServerParam("hsShopItems");
        for (var i = 0; i < items.length; i++)
        {
            var orig = items[i];
            //console.log(orig,id);
            if (orig.id == id)
            {
                var item = new HsCartItem(orig.title);
                for (var field in orig)
                    item[field] = orig[field];
                //console.log(item);
                return item;
            }
        }
        return null;
    }

    /**
     * 
     * @returns {HsCartItem[]}
     */
    this.getItems = function ()
    {
        return this.items;
    }


    this.getSubtotal = function ()
    {
        var total = 0;
        for (var i = 0; i < this.items.length; i++)
        {
            var item = this.items[i];
            total += item.getPrice() * item.getQuantity();
        }
        return total;
    }.bind(this);

    this.refresh = function ()
    {
        this.save();
        this.fireEvent("refresh");
        return;
    }.bind(this);

    this.save = function ()
    {
        var arr = [];
        for(var i=0; i < this.items.length; i++)
        {
            var item = this.items[i];
            var obj = {};
            obj.i = item.id;
            obj.q = item.quantity;
            arr.push(obj);
        }
        hs.setCookie("hsCartItems", JSON.stringify(arr));
        
        
    }.bind(this);

    this.addItem = function (item)
    {
        for (var i = 0; i < this.items.length; i++)
        {
            var el = this.items[i];
            if (el.isEquals(item))
            {
                el.quantity = +el.quantity + +item.quantity;
                this.refresh();
                return;
            }
        }

        this.items.push(item);
        this.refresh();
    }

    this.removeItem = function (item)
    {
        var i = this.items.indexOf(item);
        if (i === -1)
            return;

        this.items.splice(i, 1);
        this.refresh();
    }.bind(this);


    this.formPriceString = function (price)
    {
        var currency = this.getCurrencySymbol();
        var result = currency + price.toFixed(2);
        return result;
    }

    this.getCurrencySymbol = function ()
    {
        return "";
    }

    this.getItemById = function (id)
    {
        var item = null;
        $.each(this.items, function (i, el) {
            if (el.id == id)
                item = el;
        })
        return item;
    }
    this.getRow = function (item)
    {
        var row = null;
        $.each(this.items, function (i, el) {
            if (el == item)
                row = i;
        })
        return row;
    }


    this.getPriceForOneItem = function (itemId, itemQuantity)
    {
        var item  = this.getItemById(itemId);
        var price = item.price*itemQuantity;
        
        //console.log(itemId,itemQuantity,item,price);
        return price;
    }
}

