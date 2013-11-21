# Backend Action #

Version: 1.0

Adds the ability to customize actions in the backend.
The extension provides a field that will execute the specified php script when activated.

### REQUIREMENTS ###

- Symphony CMS version 2.3 and up (as of the day of the last release of this extension)

### INSTALLATION ###

- `git clone` / download and unpack the tarball file
- Put into the extension directory
- Enable/install just like any other extension

See <http://getsymphony.com/learn/tasks/view/install-an-extension/>

*Voila !*

Come say hi! -> <http://www.deuxhuithuit.com/>

### HOW TO USE ###

- Add a Backend Action field to your section.
- Set up the php script you want to execute when the button is pressed. The script location is relative to the workspace.
- In the script, you get a reference to and `Entry` object and a `Field` Object. Variable names are `$entry` and `$field`.
- Be sure to set the `$success` variable to `true` in order to mark the action as being executed.

### History ###

- 1.0 - 2013-11-13    
  First release  