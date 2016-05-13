# vivetracker
This started as a simple continuation of reddit user Gebolze's DHL scraping and daily reporting on the European Shipping megathread reddit page. However after Gebolze left shortly after receiving his Vive I decided to pickup and continue his effort. Later I expanded the functionality from simple manual reddit report posts to a fully automated website.

This is the entire sourcecode of the project, consisting of two parts.
You are free to do what you want, as long as you remember to credit my work. Improvements are also highly appreciated!

# Website
Quite self explanatory. Contains all the website elements available on http://robserob.dk/vive/

# Scripts
Contains all the server side scripts used for scraping and some manual adjustments.
It uses two scrapers, vive and revive.
## vive
Simple scraper which continues from the last found tracking number and generates new numbers. It then uses the DHL JSON service to query DHL and gather details until enough tracking numbers have been generated where DHL responds with a 404 error (not found). 
Any 404 errored tracking numbers will get added to the revive scraper for later retries.

##revive
Takes the tracking numbers the vive scraper originally did not find and retries them once again a set amount of times.

# Current serverside setup
The vive and revive scrapers are run using cron. The vive tracker is set to run every *:0, *:15 and *:45 moments and the revive tracker is set to run every half hour.

# How do you generate the DHL tracking numbers?
*This is taken from my a reddit post I made a while back [Reddit Post](https://www.reddit.com/r/Vive/comments/4cpcgc/shipping_megathread_euukch/d2q3p74)*

Well, its not perfectly incrementing. Sometimes they skip numbers and use them later (Hence why I've got the re scanner). However the numbers seem to follow the same pattern from an incremented number making them look pseudo random. In the beginning i just scanned every single tracking number, but i quickly realized that there was a pattern. The funny thing is that i guessed the pattern in only a few guesses.

If i am not mistaken the formula DHL currently uses is:
```
|x/7| * 70 + (x mod 7 * 11)
```
But yes, It's really bad practice to make numbers like these SO easy to generate, they should have been randomized. But making a system like that would be a lot harder, also considering that they regularly reuse old numbers. Having one incremented value makes it easier to provide the shippers with tracking numbers. I think i normally see well over 100.000 packages a day (And it seems Asia has its own starting point so i am not even counting them!). The time it would take to make random numbers until it doesn't hit an already existing one could introduce significant slowdown at peak times. So these pseudo random sequential numbers are a lot faster.

However luckily you cannot see the address and such using those numbers. So they aren't really useful for other people :)
