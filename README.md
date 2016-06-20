# Encrypted Memos
To get started with this app you must have bower grunt gulp cordova and electron installed globally with npm.
Secondly you must run npm update in both the project root and in www/.
Thirdly you must run bower update in www.
Then you can use gulp serve-electron or gulp serve-cordova to run the app in either electron or cordova respectively.
If you want to use PHP to host a server to store all of your encrypted memos in one place then use the master branch version of code.
Otherwise use the mobile version as all data is stored in localstorage.
If you do decide to use the backend, please note that the .htaccess file needs to be tailored to either apache or nginx depending on your preference
