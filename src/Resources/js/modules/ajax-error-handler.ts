export default class AjaxErrorHandler {
    public static handleError(jqXHR: JQueryXHR, customFn?: () => void){
        if (401 === jqXHR.status) {
            location.reload();
            return;
        }

        if (undefined !== customFn) {
            customFn();
            return;
        }

        console.log(jqXHR.status + ': ' + jqXHR.statusText);
        alert('An unknown error occurred');
    }
}
