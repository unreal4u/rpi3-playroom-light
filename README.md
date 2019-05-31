Raspberry Pi 3 Light Controller
===========

This pure PHP implementation will read out an MQTT broker topic + a button. Depending on the input, it will control a relay,
allowing for a light to be turned on or off.

It will additionally inform a MQTT broker of the sensors and commands that are being sent.

Used materials
--------

The materials used for this build are the following:

TODO
* [Relay](https://www.aliexpress.com/item/Freeshipping-New-5V-2-Channel-Relay-Module-Shield-for-Arduino/1726504761.html?spm=a2g0s.9042311.0.0.27424c4dkd67Cr)
* Raspberry Pi 3b+ (Although any old rPi should be able to handle this program)

Schematics
--------

TODO

**Disclaimer**: Please ignore any errors in above drawing, I'm not an electrician. That being said, above diagram is
used to control devices dealing with AC voltage, if you don't even know what "AC" means, DO NOT use this guide and hire
somebody that knows about it!

[AC voltages *CAN* kill you!](https://www.youtube.com/watch?v=trmxzUVT2eE)  
[You don't believe me?](https://www.youtube.com/watch?v=snk3C4m44SY)

Pin layout is based on this diagram:
![GPIO pin diagram](/rpi3-gpio-pins.png)

How to run the program
--------

This program consists of 3 scripts. 

TODO

This script is run with sudo because it needs access to the GPIO. There might be more elegant ways to solve this issue,
but this one is the first one that came up to me and it works.

Other information
--------

Check out [PHP/GPIO](https://github.com/PiPHP/GPIO), without that repo, this would be impossible in its current form.
