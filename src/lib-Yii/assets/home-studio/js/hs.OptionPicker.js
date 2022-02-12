function HsOptionPicker(selector)
{
    HsEventMixture.call(this);
    this.options = [];
    this.selected = null;
    this.valueAttr = "data-option-value"
    this.value = null;

    this.init = function ()
    {
        $(selector).find("[" + this.valueAttr + "]").each(function (i, el) {
            if($(el).hasClass("disabled"))
                return;
            this.options.push(el);
            if ($(el).hasClass("active"))
            {
                this.selected = el;
                this.value = $(el).attr(this.valueAttr);
            }
            $(el).on("click", this.selectElement.bind(this));
            $(el).mousedown(function(e){ e.preventDefault(); });
        }.bind(this));

    };

    this.selectElement = function (e)
    {
        $.each(this.options, function (i, el) {
            $(el).removeClass("active");
        });

        this.selected = e.currentTarget;
        this.value = $(e.currentTarget).attr(this.valueAttr);
        $(e.currentTarget).addClass("active");
        this.fireEvent("change");
    };

    this.getValue = function ()
    {
        return this.value;
    }
    
    this.getElement = function()
    {
        return this.selected;
    }

    this.init();
}