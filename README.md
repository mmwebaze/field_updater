# field_updater Module
This module allows site builders update node fields of type integer with any data associated with them to type decimal.
This module only supports field types associated with entities of type node only.

##Usage

Once the module has been downloaded and enabled, the following steps are required in order to convert
an integer field with data associated to it to a decimal field:
- select the manage fields operation of the desired content type 
- select the edit operation for the desired integer field with data associated to it. 
- select the 'Field Settings' tab for the selected field. (The option to convert from integer to decimal is only available
if the field selected has data associated with it.)
- check the checkbox (Enable integer to decimal conversion)
- from the available drop downs select the desired precision and scale
- save the field settings