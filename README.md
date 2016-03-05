# Template attachments

This extension adds functionality to add attachments to message templates. 
You can add attachments to templates by editing the message templates under Administer --> Communication --> Message templates.
Updating a template through the e-mail screen does not work.

# Installation

Download the extension and install it through the Manage extension screen of CiviCRM. No further configuration is needed.

# Technical explanation

This extensions implements the form hooks to add upload fields to the edit message templates screen. Once a file is uploaded 
the file stored and linked to the message template. 
The file is also stored in the civicrm_file table.

When sending an e-mail this extension will check which template is used and which files belong to that template and adds them to 
the e-mail message.
