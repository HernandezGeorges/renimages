## README of Renimages

### Summary

This php program uses exif datas to **rename** files and **move** them to the right folder following the **structure Year > Month > YMD\_Hi\_XXX.extension**.

It **handles duplicates** files too by adding suffix to the final name *(read index.php)*.

*Former names are not stored.*


### Installation

1. copy this repo into your local server
2. rename master folder *renimages*
3. if your folders aren't in full access mode, in command line do a *chmod 777* on the folders *sources* and *renamed*
4. configure your local server to be able to handle the *renimages* directory


### Usage

1. copy the photos you want to sort and rename to *sources*
2. load your local url in a browser
3. if you see, the button *lancer le renommage*, you're ready to go
4. click on the button and that's it !
