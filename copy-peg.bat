rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\action" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\dashboard" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\data-storage" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\editor" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\includes" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\resources" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\settings" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\viewer" /Q
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\action" "C:\Users\egart\Documents\GitHub\pegasus\action" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\dashboard" "C:\Users\egart\Documents\GitHub\pegasus\dashboard" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\data-storage" "C:\Users\egart\Documents\GitHub\pegasus\data-storage" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\editor" "C:\Users\egart\Documents\GitHub\pegasus\editor" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\includes" "C:\Users\egart\Documents\GitHub\pegasus\includes" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\resources" "C:\Users\egart\Documents\GitHub\pegasus\resources" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\settings" "C:\Users\egart\Documents\GitHub\pegasus\settings" /H /E /Y /K /I
xcopy "C:\Users\egart\AppData\Local\xampp\htdocs\viewer" "C:\Users\egart\Documents\GitHub\pegasus\viewer" /H /E /Y /K /I