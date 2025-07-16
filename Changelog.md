# Changelog

## Version v3.1.12 (2025-07-16)

### Fixes

- add missing fixtures in tests (1b1a43e8)
- update deprecated methods with new equivalent (fa4fbfc6)

### Chores and tidying

- update packages (c184400b)

### Other

- use DROP column instead only DROP (1e810849)

## Version v3.1.11 (2025-07-14)

### Fixes

- fix parsing error on empty transactions (49ef76b9)
- fix missing 'group by' clause in 'only_full_group_by'-sql_mode (5df4a7b5)
- fix migrations (0412ec62)
- fixed all cypress tests (b75d3868)
- fixed all backend tests (c6242ace)
- fixed some backend tests (0c88789b)
- fixed frontend unit tests (c1652673)
- fixed guestButton (c2313f81)
- fixed modal that shows participants for an event (d8eb7a9c)
- fixed backend structure of events works but destroyed join and leave methods (6649f0ad)
- fixed rebase error oops (f2727994)
- fixed eventParticipation collection in Controller, changed getEvents() and added getEvent(),changed DataFixtures, updated Day.php (5cf3252d)

### Refactoring

- refactoring but the backend tests broke (ae1a3b15)

### Chores and tidying

- update cs-fixer because of differing versions locally and ci (ee73ca7c)
- updated participation counter and checkbox to be updated via mercure at the correct time (36693449)
- Updated id generation to avoid duplicate key problem, event link is generated correctly, event is shown, guest can sign in to an event (301e90c9)
- updated join and delete methods (8af3ec98)
- updated structure of event dictionary and started to debug in order to find mistake in doctrine for the getWeeks() (f7000859)
- updated event id in data set migration (dc04eddc)
- updated events in test (12a104c8)
- updated api calls for events now including event id, added getEventIdBySlug Method to fetch eventId by its title (0cec3ec2)
- updated methods in EventController.php (b1aa745a)
- updated dashboard to show both events (not working yet) (8918f28b)
- updated fixture and tried to adjust the files to the new EventParticipation Dictionary (c6ac58e1)

### Other

- change costs output to always output full username (68306eb6)
- implement feedback (c2c80bb6)
- remove defined charset (c4e63c8d)
- change adding roles from event listener to migration (8797a6ca)
- added second event to the edit week site in the frontend, second event input opens when you input a first one (77c0e79c)
- add automatic checking for presence of user roles in db to mitigate access lockout on buggy migrations (68b47b64)
- added second event to the edit week site in the frontend, second event input opens when you input a first one (ca191f3c)
- final refactoring changes (e76e1b4f)
- added seperate methods for event and meal invitation (7074abfe)
- edited the mapping of event, url generation works now (6b39eb9f)
- started to edit guest invitation (277c7c5a)
- ran prettier (f96bb1fd)
- added events to the notification of updated/new week in mattermost (d80e5126)
- added events to dashboard (2a61dfb1)
- added second event to the edit week site in the frontend, second event input opens when you input a first one (d4f07b8f)
- enabled deleting of events and updated the frontend to always show two empty boxes for events (4df77d99)
- Ändern von Events funktioniert (52223e6d)
- deleted comments and updated formatting (8eca1904)
- Event is shown in the frontend - updated weekStore and the fetch for events from backend (68dcaf9f)
- deleted logger (f56087dc)
- started to update frontend to show events (fbe68928)
- Added possibility to input two events for one day, doesn't show in the frontend yet (e7e12266)
- event is input into the database, just one event at a time works at the moment (0cbfd519)
- changed MenuDay.vue, two events can now be input (70bcc2e2)
- migrated db, added event collection to Day.php (77943ba8)
- added email as a variable (eb09bd19)
- made the design of the event selection responsive (df8db137)
- added second event to the edit week site in the frontend, second event input opens when you input a first one (e1e36f65)
- added email to user profile (81a56ba2)

## Version v3.1.10 (2025-06-26)

### Fixes

- fix empty weeks on menu creation (f53d31e5)

### Chores and tidying

- Update token (71d3facb)
- Update token (ac7257a4)

### Other

- Bump node from 23 to 24 (34b4c0e6)
- Bump vite from 6.2.4 to 6.3.4 in /src/Resources (0a860d20)
- Bump undici from 5.28.5 to 5.29.0 in /tests/e2e (fc1e03a7)
- add ordering of weeks by startdate to prevent false ordering by week id (0773fec2)

## Version v3.1.9 (2025-03-31)

### Fixes

- fix typos, localization related issues and error messaging (f84b6222)

### Other

- Bump vite from 6.2.2 to 6.2.4 in /src/Resources (b6d73f93)

## Version v3.1.8 (2025-03-21)

### Chores and tidying

- updated PrintLink to only show up when the current day is enabled to prevent an error on navigation to PrintableList (f3b70ee3)

### Other

- Bump pentatrion/vite-bundle to be compatible with vite-bundle-symfony and perform migration because of new major version (7fe5573a)
- Bump jspdf from 2.5.1 to 3.0.1 in /src/Resources (f6084aec)
- Bump vue version (9950021e)
- Bump vue-i18n from 9.14.2 to 9.14.3 in /src/Resources (22f790c8)
- Bump esbuild, vite-plugin-symfony, @vitejs/plugin-vue, vite, vite-plugin-vue-devtools and vitest (228608ab)
- Bump axios from 1.7.4 to 1.8.2 in /src/Resources (e385b6c5)
- Bump @babel/runtime from 7.24.8 to 7.26.10 in /src/Resources (59255683)
- Bump undici from 5.28.4 to 5.28.5 in /tests/e2e (84bfd985)
- Bump canvg from 3.0.10 to 3.0.11 in /src/Resources (2285edfb)
- add type to method argument (b7447af7)
- changed headline matching date to regex to prevent test from breaking on new year (4d0eb355)
- added fe components to create a guest and add them to a meal (30bda04d)
- Bump node from 20 to 23 (07219eb0)
- replace abandoned paypal sdk (d0ad642a)
- added endpoint to create guest profile (7b51fa97)
- Bump vite from 5.3.4 to 5.4.14 in /src/Resources (3b1de60b)

## Version v3.1.7 (2024-12-16)

### Other

- Bump nanoid from 3.3.7 to 3.3.8 in /src/Resources (e41c4050)
- set new prices (1af33e44)
- Bump micromatch from 4.0.7 to 4.0.8 in /src/Resources (210c1d93)
- Bump @intlify/core-base and vue-i18n in /src/Resources (768e28c4)

## Version v3.1.6 (2024-11-26)

### Fixes

- fix linter messages (19da80a0)
- fixed linting (beb94c5f)
- Fix tv view when week or day is disabled but has participants (68dcde83)

### Chores and tidying

- update packages (bef3b09c)

### Other

- Bump cross-spawn from 7.0.3 to 7.0.6 in /tests/e2e (e166ff6a)
- add check if Lang is undefined (d0366957)
- Bump micromatch from 4.0.7 to 4.0.8 in /tests/e2e (f983568e)
- Bump rollup from 4.18.1 to 4.22.4 in /src/Resources (e723a467)
- Language selection is now stored in localStorage in browser and will appear as choosen on the next login. (3e32bce6)
- SSO: Use client specific user roles (#515) (5e564dd5)

## Version v3.1.5 (2024-08-21)

### Fixes

- fixed formatting (53e33911)
- fixed a bug that prevented booked Guest meals to be displayed in the participant list (ad609b22)

## Version v3.1.4 (2024-08-15)

### Security Fixes

- security fix for vue-template-compiler (e275564e)

### Fixes

- fixed a bug that occured when trying to join a combi meal when no more meals for the day could be booked (aabf1138)
- fixed a bug that prevented setting the servingsize correctly and added updating combimeals after setting serving size (73d61478)
- fixed missing shadows by removing global css (ab236a00)
- fixed several security issues (f03f27c2)
- fixed typing issue (1af59607)
- fixed broken cypress tests (2c0be0af)
- fix for flaky test (5faf6f2c)
- fixed missing shadow on GuestDay (5d754d82)
- fixed broken button coloring (ce3bf666)
- fixed missing background on participation counter (0e94f66e)
- fixed cypress tests (d9f40df9)
- fixed formatting (716fb012)
- fixed remaining typing problems (bf649ef8)
- fixed several typing issues (430cf7e2)
- fixed several typing issues (dee97908)
- fixed some typing problems (7af806e1)
- fixed ddev setup process (7246021a)
- fixed make commands / npm commands (16e980ce)
- fixed some typing problems (37781bd4)

### Chores and tidying

- updated axios because of a vulnerability that allowed server-side request forgery in axios (61a20844)
- Updated Readme (6bfe1e12)
- updated folder to Recources (2cbb1cf2)

### Other

- changed that users with a balance of 0 can be shown in the costs tab (e47a49d8)
- added checks for updating menus and setting serving size of dishes, also added verbose errormessages for failed checks (3735fd53)
- removed tcpdf, because it is no longer used to create pdfs and has a vulnerability (d7e48eea)
- potential fix for docker build warning (fc5ea854)
- another fix for broken cypress test (a2fdcc9d)
- removed commented code (a6ef925f)
- added verification in guest component and added flashmessage to inform users when they exceed the max number of meals per day (3e527539)
- added required optional parameter (b100d966)
- adjusted formatting (1caecf7a)
- added check for max meals in backend (ed2584d0)
- removed console.logs (77c58f21)
- added Guest label to displayed lists of participants (d69ce782)
- implemented lazy loading of views to reduce initial bundle size (133d23a9)
- changed node variable to vite env variable (6787e570)
- reverted cypress version as a potential fix (d8ea4a5c)
- changed yarn to npm in cypress build (7de083a8)
- adjusted github actions pipeline to use vitest and vue-tsc (0d5c7f90)
- migrated jest tests to vitest (f0482cc4)
- started jest to vitest migration (ab1aab41)
- finalised vite setup (884adab0)
- added dev config (5ea42daf)
- added vite config (7b96720e)
- added symfony-vite-bundle (09437f5a)
- adjusted imports to be compatiuble with vite, added missing packages and added some null checks (80483e4c)
- copied files from webpack package (72dc98cb)
- initialized basic vite config (afcbf06a)

## Version v3.1.3 (2024-08-05)

### Fixes

- fixed a overflowing image bug that was caused by a not supported tag in firefox 88 and fixed not truncated text (65827bae)

### Chores and tidying

- updated deprecated docker-compose command (1fab58b3)

### Other

- potential fix for flaky cypress test (0e5ec9be)
- formatted code (935f0f70)

## Version v3.1.2 (2024-07-24)

### Fixes

- fixed cypress test (1f7df921)
- fixed code smell (16f748d0)
- fixed formatting (0715dd61)
- fixed scrollbar appearing on the tv screen (a3b43ed0)
- fixed disappearing participationcounter (2f033556)
- fixed vanished meal checkbox on guestpage (80eb940e)
- fixed lockdates fixture to match expected week (183476c8)
- fixed cypress tests (6fb7c95c)

### Other

- Bump socket.io-parser from 3.3.3 to 3.3.4 in /tests/e2e (b60a14b7)
- adjusted tests (a7287dc6)
- removed a fixture user from the pool of participants (067b2435)
- added filtering of dishes by diet (13502c61)
- added variations component and added cypress test for guest invitation (caf98a25)
- cleaned up unused guest code (ca70fa0c)
- added tests to verify VegiIcon is working (47f658a9)
- potential fix for cypress test (b4aa8245)
- formatting fix (547f466e)
- Intermediate extended tests (ae86f09b)
- created migration for new diet attribute and fixed broken tests (47d97cb9)
- adjusted styling of VeggiIcon, added tooltip for VeggiIcon, added Diet to TV-Screen (b9a1d518)
- added icons for vegan and vegetarian, adjusted interfaces of apicontroller to also include diet attribute, adjusted tests to match the interfaces (c909e0fa)
- formatting fixes and code smell fixing (c7eb650b)
- added diet input for variations and updated the state update process (c2837242)
- changed dish input form to allow for inputting a diet type (dec21655)
- adjusted dish interface to accomodate diet type, updated interface check to work with diet type and fixed unit tests that broke due to the changes (ed72f133)
- added attribute Diet to Dish and extended fixtures to accomodate Diet type (91332e1b)

## Version v3.1.1 (2024-07-12)

### Fixes

- fixed a code smell (d67a63a5)
- fixed a cypress test and added a new test for the fixed menu creation bug (11e97fd8)
- fixed formatting (10eeaa0d)
- fixed a bug that prevented participation limits to be set before saving a menu and fixed a bug that prevented a meal to be added to the menu after deleting the first meal of a day (add30808)
- fixed a bug that gave locked days in the dashboard the wrong minimum height (5be07bf9)
- fixed code smell (14ee9d87)

### Tests

- tests done, ready for review / discussion (deb12368)
- test parallel build (5f69ddd7)
- test parallel build (7bb14a0b)
- test parallel build (24557dbc)
- test parallel build (bf1798d3)
- test parallel build (9e1935da)
- test prod image (5eaf6535)
- test increasing containers (fb1a8502)

### Chores and tidying

- updated faulty method signiture, added automatic reloading on session timeout, added popup to indicate a session timeout (a5aab746)
- updated frontend validation for event joining and added validation for event joining in the backend (e6c9577c)

### Other

- added text and styling to SessionCheckerPopup, exported session checking from checkActiveSession (ddc29ea7)
- - refactored MealState to be computed in the frontend (51cb729d)
- started working on fixing participation input (9c94546a)
- added redirect to mitigate faulty app state (911b29af)
- added and configured cypress-split to parallelise the cypress tests (517d4cbe)
- decreased the number of weeks and meals to load as fixtures (2bb8d7d3)
- Changed creation of random users in fixture loading in dev environment to speed up fixture loading (17402d57)

## Version v3.1.0 (2024-07-03)

### Fixes

- fixed code formatting (06ab4b25)
- fixed typing error (1c038abd)
- fixed error that was created during initial commit (ba18b25f)
- fixed time dependend test that broke in july (bde3deeb)
- fixed broken tests and adjusted fixture (990b66f6)
- Fix snyk file (7f6f855d)
- fixed a bug that occured on creating a new week, when getting the lockdates for the to be created week (c58bbdca)
- fixed a bug that caused costs not to be visibly updated if cash was put into the account (3dbc88aa)
- Fix phpmd message (efecba9e)
- Fix multiple types overlap errors (c9ccf14d)
- Fix routing and types (f18d643a)
- Fix linter messages (3565e6e9)
- fixed pipeline error - Icon Cancel had unnecessary @close property (19451008)
- Fix code analysis issues (ff700c1a)
- Fixed lintingproblem with make run-prettier (ff8968d7)
- fixed layout for isLocked Day.vue (9085fa4d)

### Chores and tidying

- update nodejs to 22, update packages (f5d68fdb)
- update lock file (873e96e4)

### Other

- reverted previous changes to costs type in the frontend and changed the type of costs to float in the backend (e8338b21)
- Revert "Bugfix/#264597 costs type" (6d264294)
- removed vue3-html2pdf due to security vulnerabilities (54eb9d8a)
- adjusted created pdf file (aeb03b67)
- replaced vue3-html2pdf in costs and finance (82c70464)
- added service to create pdf files from its slot content (93064a7b)
- added jsPdf and domtopng dependencies (003513d7)
- Bump tecnickcom/tcpdf from 6.7.4 to 6.7.5 (5f5686bd)
- remove phpcollection/phpcollection package (7ec5907c)
- Bump braces from 3.0.2 to 3.0.3 in /src/Resources (475ff2e6)
- suppress couplingBetweenObjects warning (8d65e3bf)
- Bump docker/build-push-action from 5 to 6 (49331af7)
- Bump ws from 7.5.9 to 7.5.10 in /src/Resources (d669c8d4)
- Icon cancel added @close property with correct parameters (a011947f)
- wip (b3920808)

## Version v3.0.8 (2024-06-13)

### Fixes

- Fix wrong balance format in prefilled payment field (292ee611)

## Version v3.0.7 (2024-06-11)

### Fixes

- fixed tests (b6a2e668)
- fixed formatting (f7ba4fe7)
- fixed a test (f2b9a3b5)
- fixed a bug that caused profiles with 0€ as a balance to be displayed on the cost list. Also fixed tests, that were no longer functioning after the first fix (3d99f507)
- fixed failing costs cypress tests (1f56797e)
- fixed code smells and a test that broke on a specific date (07.05) (ad40428c)

### Other

- changed token to fix pipeline (6c35e1d2)
- initial fix for weird input behaviour (e5399249)
- added felix comitt to fix test bug (d10b382a)

## Version v3.0.6 (2024-04-15)

### Other

- Bump tecnickcom/tcpdf from 6.6.2 to 6.7.4 (26049fd8)
- Bump tar from 6.2.0 to 6.2.1 in /src/Resources (ed2d5405)

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

