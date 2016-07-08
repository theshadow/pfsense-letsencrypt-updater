Pfsense Let's Encrypt Updater

This is a simple project based on this [post](https://thedevops.party/lets-encrypt-ssl-certificate-on-pfsense-2-3/). 
The goal is to make it automatically update the pfsense configuration with the new certs as they expire. This requires
two components. First is a method of generating valid SSL certificates. Second is the ability to update the [pfsense](https://www.pfsense.org/)
configuration.

To accomplish the first task we're going to use the (acme.sh)[https://acme.sh] tool to generate valid certificates.
So install that and make sure it's working. For now, this tool doesn't do the initial installation of your certificate.
You'll have to use the Certificate Manager outlined in the blog post. Once this is done you'll want to move on to the 
next step.

Download this project to somewhere on the your pfsense router. Once you have you'll want to set up a cron job to execute
the ```letsencrypt-cron-task.sh``` when your certificate is going to expire. An example is shown below.

```
0 0 * * * sh "/path/to/pfsense-letsencrypt-updater"/letsencrypt-cron-task.sh "www.example.com" "/config/config.xml" "Let's Encrypt Cert" "/path/to/pfsense-letsencrypt-updater/lpcu.php"
```

After that every time the cron job kicks off it will call the script passing in the correct values which will then backup
the pfsense configuration file, make the necessary updates, and write the changes out. Lastly it will remove a cache file
which should trigger pfsense to reload the configuration. If all goes well it should be pretty seamless.

Installation

## Cloning the repo

```git clone https://github.com/theshadow/pfsense-letsencrypt-updater```

License

MIT License
Copyright (c) 2016 Xander Guzman

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.