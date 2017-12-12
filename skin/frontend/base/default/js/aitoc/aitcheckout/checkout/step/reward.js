var AitReward = Class.create(Step,  
{
    initReward: function(rewardContainerId)
    {
        this.initEvents(rewardContainerId);                        
    },

    afterInit: function()
    {
        this.initReward(this.container);
        this.setReloadSteps(['review']);
    }
});