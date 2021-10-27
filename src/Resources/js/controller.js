let Controller = function (view) {
    console.log('Initializing controller...');
    if (typeof view == 'string' && view !== '') {
        console.log('Loading view: "'+view+'" ...')
        this.getView(view);
    }
};

Controller.prototype.getView = function (viewName) {
    const viewFile = './'+viewName+'.js';
    const context = require.context('./views', false, /.*\.js$/);

    // try {
        const viewObj = context(viewFile);
        console.log(viewObj);
        new viewObj.default();
    // } catch (err) {
    //     console.log(err);
    // }
};

export { Controller };
