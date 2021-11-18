import Switchery from 'switchery.js';

Mealz.prototype.initHiddenUsersToggler = function () {
    const self = this;
    let checkbox = document.querySelector('#toggle-hidden-users')
    if (checkbox === null)
        return

    let state = checkbox.checked

    // reload cached state
    let showHiddenUsers = localStorage.getItem('showHiddenUsers')
    if (showHiddenUsers !== null) {
        let cachedState = showHiddenUsers === 'true'
        if (state !== cachedState) {
            state = cachedState
            if (cachedState) {
                checkbox.setAttribute('checked', 'checked')
            }
            else {
                checkbox.removeAttribute('checked')
            }
        }
    }

    self.toggleHiddenUsers(state)

    new Switchery(checkbox, {
        onChange: function (state) {
            self.toggleHiddenUsersChangeHandler(state);
        }
    });
};

Mealz.prototype.toggleHiddenUsersChangeHandler = function (state) {
    Mealz.prototype.toggleHiddenUsers(state)
    localStorage.setItem('showHiddenUsers', state)
};

Mealz.prototype.toggleHiddenUsers = function(state) {
    let userRow = $('.user-hidden')
    state ? userRow.show() : userRow.hide()
}