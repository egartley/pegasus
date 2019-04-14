rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\dashboard" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\data-storage" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\editor" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\includes" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\resources" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\viewer" /Q
rmdir /s "C:\Users\egart\Documents\GitHub\pegasus\submit" /Q
xcopy "C:\Users\egart\XAMPP\htdocs\dashboard" "C:\Users\egart\Documents\GitHub\pegasus\dashboard" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\data-storage" "C:\Users\egart\Documents\GitHub\pegasus\data-storage" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\editor" "C:\Users\egart\Documents\GitHub\pegasus\editor" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\includes" "C:\Users\egart\Documents\GitHub\pegasus\includes" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\resources" "C:\Users\egart\Documents\GitHub\pegasus\resources" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\viewer" "C:\Users\egart\Documents\GitHub\pegasus\viewer" /H /E /Y /K /I
xcopy "C:\Users\egart\XAMPP\htdocs\submit" "C:\Users\egart\Documents\GitHub\pegasus\submit" /H /E /Y /K /I