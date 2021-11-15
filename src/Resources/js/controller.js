let Controller = function (view) {
    if (typeof view == 'string' && view !== '') {
        this.getView(view);
    }
};

Controller.prototype.getView = function (viewName) {
    const viewFile = './'+viewName+'.js';
    const context = require.context('./views', false, /.*\.js$/);
    const viewObj = context(viewFile);
    new viewObj.default();
};

export { Controller };
