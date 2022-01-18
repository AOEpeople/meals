let Controller = function (view) {
    if (typeof view == 'string' && view !== '') {
        this.getView(view);
    }
};

Controller.prototype.getView = function (viewName) {
    const viewFile = './'+viewName+'.ts';
    const context = require.context('./views', false, /.*\.ts$/);
    const viewObj = context(viewFile);
    new viewObj.default();
};

export { Controller };
