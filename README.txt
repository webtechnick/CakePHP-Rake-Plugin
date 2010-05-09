CakePHP Rake Plugin

Author: Nick Baker
License: MIT
Version: 1.0

This plugin aims to give the user a rake like command line testing experience by modifing the output of the testsuite shell

h1 Install

copy the plugin into your *app/plugins/rake* directory

h1 Usage

- cake rake help                   # show the help menu
- cake rake app all                # run all of your app tests rake style
- cake rake app case models/model  # run the model test
- cake rake app all verbose        # append verbose to any valid test command to see all passing tests.