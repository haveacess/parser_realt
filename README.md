This project scraping (with use mobile API) and consolidate information on realty sites. 

## Where search realty:
Sites, where parser be search realty: avito, cian.

## Prepare for start
This project use some external services. Please, follow the instruction below.

Step 1. Get credentials.json - private key for access to Google Sheets.
[Receive credentials.json in you google account](https://docs.google.com/document/d/1RKNU2JlSqSIrdtrcI2cAJOTEkXOeA3XH8JOldWun2FU/edit?usp=sharing).  
After receive credentials.json transfer this file in folder **config**. Filename **credentials.json**  

Step 2. Prepare you google sheet - make copy [this sheet](https://docs.google.com/spreadsheets/d/1VnxbFWptTX16JKMXG_IJmUizMdcatmGv5_jBA9U8jwg/edit?usp=sharing) in you google account  
And then open file **config/settings.php** and edit variable **sheet_list**, point out identificator google sheet  
1Vt3w5UHw21t6JI8QmfkByQS2zo3X_fA89VyFMDWUmJc - example of id

Step 3. Settings you database  
This tool be save all in database before uploading in Google Sheet.  
Please create database in own server and then export file **realt_dump.sql**
*This file contain structure data*  
And then open file **config/settings.php** and edit variable **db_config**, point out all for access to you database

Step 4. Point out your proxy keys (if this need)  
File **config/settings.php**, variable **proxymarket_apikey**. Service: [proxy.market](https://proxy.market/)  
*Proxy6 now don't using

Step 5. Change search conditions (if this need)
Visit in site, set mobile version, open debugger Network, select any location and need filters, scroll to down page (or press "Показать еще"), and find request in inspector that contains word "search". You need copy sended data as json and put in project

## Install dependencies use composer
cd <folder_project>  
composer install  

## Run project
cd <folder_project>  
php main_v2.php - **Launch in cli mode**
