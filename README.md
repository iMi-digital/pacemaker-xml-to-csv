Description
===========

Plugin for https://m2if.com Pacemaker Magento Import Framework

Lets you use XML files which are transformed by your XSLT files to CSV.

Precondition
============

You need any XML source file which can be converted to a Magento import structure using an XSLT file you provide.
The result of the transformation must look like this:

    <Items>
        <Item>
            <column1>value</column1> 
            <column2>value</column2>
        </Item>
        <Item>
            <column1>value</column1> 
            <column3>value</column3>
        </Item>
    </Items>

Example configuration.json excerpt
==================================

    "operations": {
        "general": {
            "eav_attribute": {
                "convert-xml": {
                    "plugins": {
                        "converter": {
                            "id": "import.plugin.xml.transform",
                            "params": {
                                "exportable-artefact-type": "attribute-import",
                                "input-xml": "current/attributes.xml ",
                                "transformation-xslt": "dev/importer/xslt/attributes.xslt"
                            }
                        }
                    }
                }
            }
        }
    },
    "shortcuts": {
        "ce": {
            "eav_attributes": {
                "add-update": [
                    "general/eav_attribute/convert-xml",
    ...
