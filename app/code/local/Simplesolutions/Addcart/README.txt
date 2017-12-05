IndexController appears to be the only functioning file of this extension...
the MessagesController is a completely different module namespace.

-----

Appears to be a custom solution to adding items to the cart from the
"Cabinet Designer" and other custom "wizards"? 

The controller uses the frontend namespace "addcart" which is referenced by the 
following HTML and JS files:

    designer/cabinet-designer.html
    designer/cabinet-designer.js
    designer/cabinet-designer.min.js
    designer/mangle.js

    dev/AJAX Examples/aventos_orig.js
    dev/AJAX Examples/aventos.js
    dev/AJAX Examples/aventos2.js
    dev/AJAX Examples/aventos3.js

    dev/Decorative Hardware/js/designer.js

    dev/Ladder/ladder-script.js
    dev/Ladder/ladders.js

    dev/ScrewFinder/scripts/ajax.js
    dev/ScrewFinder/scripts/purchaseForm.js

    skin/frontend/enterprise/cshardware/js/aventos.js
    skin/frontend/enterprise/cshardware/js/door_backup.js
    skin/frontend/enterprise/cshardware/js/door.js
    skin/frontend/enterprise/cshardware/js/ladder.js
    skin/frontend/enterprise/cshardware/js/screwtocart.js

    skin/landing/enterprise/cshardware/js/aventos.js
    skin/landing/enterprise/cshardware/js/door.js
    skin/landing/enterprise/cshardware/js/ladder.js

    stairs/cabinet-designer-min.js
    stairs/cabinet-designer.html
    stairs/cabinet-designer.js

Further research is required to see if these files are currently being loaded
and used in the website.