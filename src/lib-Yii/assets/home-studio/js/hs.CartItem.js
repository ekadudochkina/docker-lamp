/**
 * Товар
 * 
 * @see {HsCart}
 * @returns {HsCartItem}
 */
function HsCartItem()
{
    this.id = null;
    this.title = null;
    this.price = null;
    this.quantity = 0;
    this.image = null;
    this.price = 0;
    this.size = null;

    this.getTitle = function ()
    {
        return this.title;
    }.bind(this);

    this.setSize = function(size)
    {
        this.size = size;
    }
    
    this.setId = function(id)
    {
        this.id = id;
    }.bind(this);
    
    this.getId = function()
    {
        return this.id;
    }.bind(this);
    
    this.setPrice = function (number)
    {
        this.price = number;
    }.bind(this);

    this.getPrice = function ()
    {
        return this.price;
    }.bind(this);

    this.hasImage = function ()
    {
        return this.image !== null;
    }.bind(this);

    this.getImage = function ()
    {
        return this.image;
    }.bind(this);

    this.setImage = function (url)
    {
        this.image = url;
    }.bind(this);



    this.getQuantity = function ()
    {
        return this.quantity;

    }.bind(this);

    
    this.setQuantity = function (number)
    {
        this.quantity = number;
    }.bind(this);

    /**
     * Загружает данные о товаре с сервера.
     * Данные должны быть предоставлены объектом ShoppingCart.
     * 
     * @returns {Boolean} True, если товар был загружен
     */
    this.loadMainItem = function()
    {
        console.log("Loading item",this);
        var el = $(".shoppingCartItem.main").get(0);
        if(!el)
            return false;
        var title = $(el).find(".title").html();
        var description = $(el).find(".description").html();
        var price = parseFloat($(el).find(".price").html());
        var image = $(el).find(".image").html();
        var id = $(el).find(".id").html();
        this.title = title;
//        this.description = description;
        this.id = id;
        this.image = image;
        this.price = price;
        
        return true;
    }
    
    this.isEquals = function(item)
    {
        if(this.id == item.id && this.size == item.size)
            return true;
        return false;
    }
}