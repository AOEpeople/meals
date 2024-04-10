# Changelog

## Version v3.0.5 (2024-04-10)

### Fixes

- fixed a bug that could show combined-meal in the MenuPaticipationPanel (735b6415)

## Version v3.0.4 (2024-04-03)

### Chores and tidying

- updated paypal plugin to a newer version and adjusted TransactionPanel to work with the updated packages (fbfc5e3a)

## Version v3.0.3 (2024-03-27)

### Fixes

- fixed prettier linting (fcd94302)
- fixed a bug that caused the visible and the logic state of a meal to divert on meals that reached the max participants (2e37911a)
- fixed prettier linting (ff9cc0ac)
- fixed a bug that prevented meals with offers from being taken in some circumstances (e0812851)
- fixed a bug that prevented people from leaving meals that reached their limit (421ad9d6)
- fixed prettier linting (6d0ec2c8)
- fixed a bug that could trigger a click event, when clicking outside of the cancel button in menu (b2e9ab15)

### Other

- Bump express from 4.18.2 to 4.19.2 in /src/Resources (29ff9edb)
- Bump webpack-dev-middleware from 5.3.3 to 5.3.4 in /src/Resources (08738311)
- possible fix for mistyped axios response (9308d5d1)
- Bump ip from 2.0.0 to 2.0.1 in /src/Resources (2928ff06)
- added indicator coloring for lockdates that are not the standard locktime in the menu view (5a4238f5)
- Bump follow-redirects from 1.15.5 to 1.15.6 in /src/Resources (ff4e1825)
- Bump geekyeggo/delete-artifact from 4 to 5 (661e9960)
- Bump axios from 0.27.2 to 0.28.0 in /src/Resources (dddece80)

## Version v3.0.2 (2024-03-07)

### Fixes

- fixed psalm return types (9ed6f4b0)
- fixed a bug that cause offer accepted mails to be send to the participant and not the offerer (307466f9)

### Other

- Bump actions/setup-go from 4 to 5 (b292989e)
- Bump semver from 7.5.0 to 7.6.0 in /tests/e2e (c9d30d17)

## Version v2.3.9 (2023-07-06)

### Fixes

- Fix installed version of semver, sill exists in babel-loader because of find-cache-dir 3.3.2 (37374d90)
- Fix sca (90c6c677)

### Chores and tidying

- Update yarn packages (d54b8b46)
- Update stylelint settings, Fix messages (bb31807c)
- Update psalm.baseline.xml (bf485790)

### Other

- use node 16 (6625377f)
- adjust oauth test (fc99dc6c)
- Add email to profile (39aebb5b)

## Version v2.3.8 (2023-05-23)

### Fixes

- Fix booking when slots are disabled (73e1dafa)

## Version v2.3.7 (2023-03-15)

### Other

- Bump webpack from 5.75.0 to 5.76.0 in /src/Resources (b64d6d67)
- Bump docker/build-push-action from 3 to 4 (2f82ba6f)

## Version v2.3.4 (2023-02-16)

### Fixes

- Fix path (936e7802)
- Fix digiest output (71973347)
- Fix code style messages (3b724a78)
- Fix digest output (26530052)

### Other

- run crond (1bc125ca)
- add cronjob (54022abf)
- Add keep-alive subscriber (de3c035a)

## Version v2.3.3 (2023-02-15)

### Chores and tidying

- update mercure (10a5937b)
- update yarn packages (3da9bfcb)
- update composer packages (fdb81139)

### Other

- replace deprecated set-output function (9008bf93)
- change dummy JWT secret to 256 bit (6fa53a3f)
- Bump json5 from 2.2.1 to 2.2.3 in /src/Resources (410f7ceb)

## Version v2.3.1 (2022-11-29)

### Ops and CI/CD

- **semanticore:** add semanticore (82040500)

### Other

- [255413] disable autoselection when slot already is choosen (b3e352a6)
- [255367] first offered meal should be taken (bb7d4635)
- Bump geekyeggo/delete-artifact from 1 to 2 (e8e168ac)

