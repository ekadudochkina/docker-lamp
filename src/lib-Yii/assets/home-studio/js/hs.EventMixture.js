function HsEventMixture()
{
    this.events = {};

    this.on = function (name, func)
    {
        if(!this.events[name])
            this.events[name] = [];
        this.events[name].push(func);
    }.bind(this);

    this.fireEvent = function (name)
    {
        if (this.events[name])
            for(var i=0; i < this.events[name].length; i++)
                this.events[name][i]();
    }
}