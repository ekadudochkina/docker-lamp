function HsCounter()
{
    HsEventMixture.call(this);
    this.value = 0;
    this.inputElement = null;
    this.plusElement = null;
    this.minusElement = null;
    this.min = 0;
    this.max = 100000000;

    this.getValue = function ()
    {
        return this.value;
    }.bind(this);

    this.setMinimum = function (number)
    {
        this.min = number;
        if(this.getValue() < number)
        {
            this.setValue(number);
        }
    }.bind(this);

    this.setMaximum = function (number)
    {
        //Тут отключила счетчик товаров
        //this.max = number;
        this.max = 10000000000000;
        if(this.getValue() > number)
        {
            this.setValue(number);
        }
    }.bind(this);

    this.setValue = function (number)
    {
        this.value = parseInt(number);
        if(this.value < this.min)
        {
            this.value = this.min;
        }
        if(this.value > this.max)
        {
            this.value = this.max;
        }
        this.refresh();
    }.bind(this);

    this.setInput = function (el)
    {
        this.inputElement = el;
        var self = this;
        $(el).on("keyup change",function(){
           var val = $(this).val();
           self.setValue(val);
        });
    }.bind(this);

    this.setPlusButton = function (el)
    {
        this.plusElement = el;
        $(el).on("click", this.addNumber);
        
        //Не делаем  выделение
        $(el).mousedown(function(e){ e.preventDefault(); });
    }.bind(this);

    this.setMinusButton = function (el)
    {
        this.minusElement = el;
        $(el).on("click", this.removeNumber);
        
         //Не делаем  выделение
        $(el).mousedown(function(e){ e.preventDefault(); });
    }.bind(this);

    this.removeNumber = function ()
    {
        // console.log(this.min+1,this.value);
        if (this.value <= this.min)
        {
            this.fireEvent("min");
            return;
        }

        this.value--;
        this.refresh();
    }.bind(this);

    this.addNumber = function ()
    {
        if (this.value >= this.max)
        {
            this.fireEvent("max");
            return;
        }

        this.value++;

        this.refresh();
    }.bind(this);

    this.refresh = function ()
    {
        if (!this.inputElement)
            return;

        $(this.inputElement).val(this.value);
        this.fireEvent("change");
    }.bind(this);

}