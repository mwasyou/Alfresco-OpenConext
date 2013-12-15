Alfresco-OpenConext
================================

Just in Time Script for SAML2 Authentication trough Shibboleth SSO and VOOT based groups for Alfresco.

Product:		Alfresco 
Tested Version: 4.2C 
Platforms:		Apple, Linux, Windows (this manual is written for Linux)

Alfresco Community Edition, Commercial support and hosted version available 
License: GNU Lesser GPL 
Website: http://wiki.alfresco.com/

This document describes in detail the steps to unlocking a federated Alfresco Share, ust in time account creation and the use of a axternal group provider.The method described in this document uses (mod) Shibboleth, Apache, Alfresco. This guide is intended for Alfresco 4.2C and Ubuntu 12.04 (LTS) installation. For different systems, the instructions may have to be altered.

Preparation
-------------------------
Istalleren Apache + SSL



Installeren Shibboelth

Configureren Shibboleth

Testen shibboleth


Install Alfresco
-------------------------
* [Download](http://www.alfresco.com/products/community) Alfresco Community Edition.
* Install alfresco as described [here](http://docs.alfresco.com/community/index.jsp?topic=%2Fcom.alfresco.community.doc%2Ftasks%2Fsimpleinstall-community-lin.html).

Configure Alfresco to use Shibboleth
-------------------------
Edit the file /opt/alfresco/tomcat/conf/server.xml

* Add `tomcatAuthentication="false”` to the following line:

	<!-- Define an AJP 1.3 Connector on port 8009 -->
	<Connector port="8009" protocol="AJP/1.3" redirectPort="8443" tomcatAuthentication="false"/>

* Edit the file /opt/alfresco/tomcat/shared/classes/alfresco-global.properties
* Add the following code to the `alfresco-global.properties` file.

	authentication.chain=external1:external,alfrescoNtlm1:alfrescoNtlm
	external.authentication.enabled=true
	# This header can be changed but should match the configuration in 
	# share-config-custom.xml
	external.authentication.proxyUserName=

* Go to the folder: /opt/alfresco/tomcat/shared/classes/alfresco/web-extension.
* Add the [file](https://github.com/Frankniesten/Alfresco-OpenConext/blob/master/share-config-custom.xml) to the folder.

Install JIT Script
-------------------------
Text


Configure JIT Script
-------------------------
Text



