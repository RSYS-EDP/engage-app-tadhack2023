# engage-app-tadhack2023
This repo contains the installation script and 3 x radisys demo apps ([radisys insurance bot](https://github.com/RSYS-EDP/engage-app-tadhack2023/tree/main/demo/install/radisys_demo_app/insurance_bot), [in-call translation](https://github.com/RSYS-EDP/engage-app-tadhack2023/tree/main/demo/install/radisys_demo_app/in_call_translator), [appointment reminder](https://github.com/RSYS-EDP/engage-app-tadhack2023/tree/main/demo/install/radisys_demo_app/appointment_reminder)) for TADHack or any similar event


## Installation

Just untar the file and run the [shell script](https://github.com/RSYS-EDP/engage-app-tadhack2023/blob/main/demo/install/install.sh) in command line.  
Follow the prompt and provide your server environment and EDP account detail, it will install all the demo apps on your web server in a second. 

This assumes the developer has already configured their web server to meet the following prerequisites to run the Engage app: 

-	Support HTTP and HTTPS
-	Support HTTP GET and POST methods
-	Support PHP 
-	Support Curl to connect through httpd 


If developer does not have a web server yet, and does not know how to create or configure the web server, below are some tutorials  
-	https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-centos-7
-	https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-nginx-on-centos-7
-	https://www.bonbon.io/how-to-enable-post-to-static-pages-running-via-nginx
-	https://stackoverflow.com/questions/25338295/getting-permission-denied-while-posting-xml-using-curl
