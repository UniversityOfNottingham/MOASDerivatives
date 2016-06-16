MOASDerivatives
===============
[![Release](https://img.shields.io/badge/stable-v1.0.0-blue.svg)](https://github.com/UniversityOfNottingham/MOASDerivatives/releases/latest)
[![License](https://img.shields.io/badge/licence-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://scrutinizer-ci.com/g/UniversityOfNottingham/MOASDerivatives/badges/build.png?b=master)](https://scrutinizer-ci.com/g/UniversityOfNottingham/MOASDerivatives/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/UniversityOfNottingham/MOASDerivatives/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/UniversityOfNottingham/MOASDerivatives/?branch=master)

This plugin allows the definition of additional image derivative sizes. It's a bit of a hack since it attaches on the back of the record (and default derivative) creation and has to undo a bit of cleanup that's already been done.

It also needs to have it's own storage adapter loaded into Omeka since the paths we're allowed to write to (i.e. derivative types. e.g. '/thumbnail') are hard coded into the default Omeka adapter.

##Configuration
The plugin needs the storage adapter it provides enabled in an Omeka configuration file `application/config/config.ini`

By default the line is commented out and refers to the S3 storage adapter. Change it as below.

```ini
[site]
storage.adapter = "MOAS_Storage_Adapter_Filesystem"
```


#####Note
* Entering the configuration line into config.ini without enabling the plugin with break Omeka.
* Enabling the plugin without entering the configuration line will break Omeka.
* To this end your best bet is to enable the plugin first and then add the configuration directive.
