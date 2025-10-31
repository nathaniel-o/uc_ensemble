hex ffa52

# GUIDELINES- CONTENT
   - Garnishes in Post-content should have prefixes with dashes 
    e.g. Garnish: Fruit- Citrus
   - Colors: Red / orange / green ( Slashes okay, capitalize first only)
   - Every Drink Needs a Post 
   - Banner Category in photo titles, in order to exlude from auto-swap
    e.g. Chili Citrus Sour
   - Periodic Curation (change photos) by hand easier than automation/review





#NEXT MEETING 
- [ ] Search Carousel gives filtered drinks .. tests good ? 
- [ ] Filtered Gallery is [page?] is Category only
- [ ] Gallery is private. "See full gallery?" not an option
- [ ] Review borders/colors: AU, EV, FP, SO, SU, SP, WI, RO   
- [ ] vary romantic BG colors: #980A4E (dark rose) and #7A083E (muted dark rose) 
    - [ ] Revisit Autumnal styles w/ new background.
- [ ] See More button -> Pop out Carousel on Post Pages 
   - [ ] Winter page is all Columns?
- [ ] Summertime bg colors okay on different screens ? 
- [ ] All pages - landscape columns layour okay ? ? 
- [ ] AU: Burning Bush Gimlet-Pop Out Image too small,also Aspect Ratio?

# TODOS 
- [ ] REVISIT Cocktail-Images. Do style changes to images' captions in GUI, affect the shortened, ~~substitute title? ~~
- [ ] Not Synced patterns allow empty page template? See drink-post-content
- [ ] Borders not working @ summer/springtime - due Gallery Block setup? 
- [ ] Other User's Posts central to Blog Page? (Or is Blog Page our Gallery? )
- [ ] Other User's Posts should be comment-style feed 
- [ ] Other user's posts may appear under Single Drink Posts? 
- [ ] Blog Home v. Gallery v. Other User's Posts? 
- [ ] Images should have Title Only for captions (just hide the Alt Text)
- [ ] No Duplicate in Carousel & if Randomizing 
- [ ] Other User's posts need be excluded from Carousel & randomizing. 
- [ ] Media library : Edit image option broken.
- [ ] this means carousel & etc. needs a check for posts
- [ ] Drink Posts need Taxonomy added for dynamic background. 
- [ ] Autumnal : CSS classes for photo rotation!! EASY !!
- [ ] Post Content : stack on mobile ! ? 
- [ ] Since drinks-plugin depends on theme's style var's anyway, maybe worth hard coding the duplicate / shared styleImagesByPageID function ? 
- [ ] Post content/Pop out: do variable text & spacing, image auto, pop-out max-size based on orientation of IMG 
- [ ] Future script testing ? (for broken images or non-closing pop-out?)
- [ ] Clear/Silver color filter wrongly gave Long Island Hibiscus Ice Tea(Red) from Martini Twist Pop-Out on EV page 
    ( hard to replicate without a script - is this unit testing? ) 
- [ ] Gallery maybe endless slider? [ Vertical for mobile seems given ]
- [ ] Summertime borders? rgba(27,245,78) ? transparent
- [ ] Klaris Smudges photo broken ? 
- [ ] SP pop-out & carousel gradient wrong ? 
- [ ] Seasonal in navbar should be based on Date . 
- [ ] Gallery == Posts Page / Blog Home? 

- [ ] Fig infused bourbon manhattan wrong dimensions

- [ ] Easy Rotation on FP ?
- [ ] Style mobile nav menu

- [ ] SO pop-out li's font color too white. 
- [ ] Springtime bg-color is #1bf54e91

- [ ] Add border to pop-out 
- [ ] img borders not working on summertime? 

- [ ] Filtered Gallery - draft as one page with search.html ? 
   

- [ ] Make Pop-Out images use ucOneDrinkAllImages

- [ ] See More button @ Carousel shows FILTERED gallery (search.html)
- [ ] add "More Drinks" button to single posts' pages. 
- [ ] Add "Home" to logo in navbar. 

- [ ] # slides limited to # search results ? 
- [ ] IF search less than carousel, DO NOT gallery ? 
- [ ] Carousel -> New Carousel 
- [ ] Search bar is different on gallery 
- [ ] despite gallery is XL carousel (& new styles ) 

- [ ] Search should consider image tags ? Import to post tags instead complicate logic ?

- [ ] SP & AU Pop-outs need review. 
- [ ] Carousel : correct # of slides ? (Just hid counter for now)
- [ ] Carousel X button sometimes doesn't work 
- [ ] Blood and Sand Riff aspect Ratio AU
- [ ] SO & SP - review Pop-Out Text Colors. 

- [ ] Not filtered carousel from pop-out  . ? 


#31 OCTOBER 25
- [ ] SO background busted ? 
- [ ] Duplicate slides not fixed yet - after 9 images, Carousel no repeat.
- [ ] Filtered Carousel allows MANY slides. 
- [ ] Search logic in modules/drinks-search. Apply better b/c E.g:
    - [ ] "Citrus" srch from FP page gave "Results n/a" on first try. 



#30 OCTOBER 25 
- [X] use homeURL to resolve local vs. live directories::SeeMore search pg
- [X] Fixed Carousel overflow in search.html context. 
- [X] Consolidated into ucSummonCarousel(context) > b/c Block Scaffolding         is still a nightmare.  
- [X] Search gives Carousel ? (filtered)
    - [X] Change default behavior. Fallback to 404 is contact-us temp'ly. 
    ~~- [ ] make smooth with JS ? Loads by AJAX to keep things serverside         instead~~

#25 OCTOBER 25
- [X] Instead of random carousel , Do "404 &/ No Results" notice ?   

- [X] Filtered gallery is just a Filtered Carousel 
- [X] Search Carousel Works same everywhere? (check then modify)
    // redirects wrong when 0 results, local site only
    - [X] "brown" gives search error - unable to replicate.  


#21 OCTOBER 25
- [X] Basic 404.html template, 404-missing template-part, & in use at 
        search.html wp:query-no-results

#15 OCTOBER 25
- [X] Remove fallback carousel fn (was always in use
    - [X] use Jetpack's swiper fns & pagination (then hide count)
    - [X] Add results counter to Carousel header 
- [X] Everyday "Banner" broken.

#14 OCTOBER 25
- [X] remove dupe Search label (Live UI)
- [X] Drinks-Plugin : filter parameter not working. 
        A: Carousel Filter now srch Post Titles, Meta fields, Tax'y Terms
    - [X] Pop-Out Carousel: AU filter gives same first slide every time. 
    - [x] DUE to logic: title THEN other data matching? 



#11 OCTOBER 25 - 
- [X] Attach filtered carousel to bullet points of Pop-Out
#10 OCTOBER 25 - JS is a "dumb client" except w/ Carousel Generation.
- [X] Cursor will index /wordpress-fresh1 ONLY
- [X] add .banner-image class to CSS (in addition to image title convention used by PHP) 
- [X] Search -> Carousel -> Varied drinks (not a relativity search)
- [X] Attach carousel action to Pop-out ul's : 
- [X] FILTER the Pop-out ul's carousel 
#9 OCTOBER 25
- [X] Search Query gives Carousel
#8 OCTOBER 25
- [X] use -fresh1 OR -new1
- [X] Header confusion : reapply stuff, txr DB post to template file. 
    - [X] search bar , 2 behaviors from THEME
    - [X] add search bar to header (For now) 
    - [X] Query loop (std) behavior ok? - is fallback ; QL not useful yet 
    - [X] MOST contexts give carousel | search.html is fallback err mode
- [X] SU column-2: 12% margin, column-3 5%. A: Done spacing in gen'l 
#7 OCTOBER 25
- [X] 2Col spacing : weird because hits edge of page. A: give margin
- [X] 3col spacing : increase gap between col's, make ALL img width: 100% by default (to fill out columns)
# 6 OCTOBER 25
- [X] add "filter" parameter to slideshow/carousel fn
- [X] unified carousel fn 
- [X] Carousels feature RANDOM(NOT clicked) (b/c users can go back anyway)
#4 OCTOBER 25
- [X] Pop-Out h1 NOT WHITE 
- [X] margin: 12.5% @ RO, SO, FP | Three column pages have 0-6%-12% margins by default. 
- [X] switch Header/Footer font sizes
#30 SEPTEMBER 25
- [X] make Pop-Out give Carousel
- [X] Carousel picks from Drink Posts, and displays featured image of selected posts. Change this to : select anything from media library?  
#26 SEPTEMBER 25
- [X] Make POP OUT default on front end. 
- [X] modify ucOneDrinkAllImages to be less conspicuous.  |  A: Faster flash, lower z-index. 
#25 SEPTEMBER 25 
- [!] REVISIT change ucOneDrinkAllImages fn() to use IMG title, not metadata (so that the tagged photo doesn't need to be the photo placed by user, for changeup toccur. | pushed to Live & shared normalization fn between plugins. 
	- [X] ... Turns out that if image placed in GUI Doesn't have metadata, WP doesn't insert title.  A:  Sync Metadata btn in Cocktail-Images :::: feature non-T1 images anywhere. 
- [X] Cocktail-Images: since we modify srcset anyway, setup server-side rebuild using Drinks' alternate images by ucOneDrinkAllImages logic.	
- [X] Use title not alt text on front end. (preserve alt text for SEO). Also would be nice to control with existing GUI options. ? 
#23 SEPTEMBER 25
- [X] juggle back to /wordpress-fresh1/. Add global page ID to theme.
- [X] drinks have metadata. Add field to Edit Post UI &button to Drinks Plugin
- [X] Rebuild Welcome Page in Site Editor (s.a.t.b.a.t. futur edits ) - Also Summertime & Springtime
#22 September 25
- [X] fix Autumnal bg again & combine git repos. 
#21 SEPTEMBER 25
- [X] Does photo change fn() need to consider dimensions? | So So See Below.
- [X] IMG dimensions after ucOneDrinkAllImages are too small. |  turns out WP serves a "srcset" of MANY resolutions by default. Quick fix : just modify the srcset=1, the full resolution image. 
#20 SEPTEMBER 25
- [X] Fireplace background doesn't work on live only? Fixed w/ refresh theme. 
- [X] npm compromised 3x this week? 
      https://www.aikido.dev/blog/npm-debug-and-chalk-packages-compromised 
#19 SEPTEMBER 25
- [X] move to /wordpress-new/ & push to Live 
#17 SEPTEMBER 25
- [X] springtime bckgrnd spacing.SVG "Fit" is controlled in theme functions.js
- [X] Margin: 12% @ S-O column2 (& others?)
- [X] Special-Occasion caption spacing. 
#15 SEPTEMBER 25
- [X] finish convert backgrounds to SVG
- [X] Autumnal : background & style fn working ? 
#11 SEPTEMBER 25
- [X] How to: CSS affects background images. ?  |  Springtime is now INLINE SVG handled by ucInsertBackground fn, so that it may be styled on the fly.  |   
#9 SEPTEMBER 25
- [X] Romantic different Heading Font?
- [X] See Variables Chart : Apply to style fn() ~~which are missing ? (re: styling function)~~
   - [X] Special Occasion heading missing text-shadow? 
   - [X] Summertime heading all green ?    
   - [X] Winter also is using std vars- has own yet? 
- [X] Special-Occasion different Heading Font? 
- [X] Winter needs ALL Variables. As winter-bg-color not wintertime-etc-etc
#8 SEPTEMBER 25
- [X] HEADER Missing Links - only? Contact-Us. |  fixed in Code editor GUI is dumb.  | 
- [X] Adjust appearance of Drink Post Content Template part : image max size, text centered , etc. 
- [X] Post content placard = Pop-out  |  requires duplicating styles or else pop-out would depend on external stuff. Includes expanding styleImagesByPageID fn into plugin. | Approximately done ; no hard link so changes must be made in two places. 
- [X] BANNER photos should not switch
# 7 SEPTEMBER 25
- [X] Welcome page H1 is all pink- needs be std-font-color
- [X] Add Post Details to Lightbox Pop out // Generally works.  |  
# 6 SEPTEMBER 25
- [X] Drink Post Content pattern broke | due to dynamic H1 php function called in obsolete template part. 
- [X] Dynamic H1 busted (Posts are okay- diverge logic by page type?) | Built page test into page id @ theme's functions.php, now style functions simpler.
- [X] Springtime dynamic h1 is using std vars- needs use SUMMER. 
# 4 SEPTEMBER 25
- [X] Carousel glitchy- doesn't close / sometimes shaded blue/orange?
- [X] Don't display duplicates in Carousel. ~~(Does Posts help this ? )~~
- [X] review H1 ; user inserts, styles dynamic. 
# 3 SEPTEMBER 25
- [X] Empress Cherry Gimlet has duplicate  Post 
- [X] Saffron Honey Gin & Tonic Post needs Featured Image
- [X] Autumnal : background working
- [X] switch working to /wordpress-new1/, include autumnal
#2 SEPTEMBER 25
- [X] Autumnal Page doesn't exist yet ? 
# 1 SEPTEMBER 25
- [X] Restore basic Carousel functionality . 
- [X] Carousel should show selected image plus four random.
# 31 AUGUST 25
- [X] Nothing Popout Doesn't work on Romantic page (due missing Featured images? Or else broken links)  |  Some DB links were referencing local. Did SQL replace now better.   
# 30 AUGUST 25
- [X]  Lighbox No Duplicate popout?
- [x] Aspect ratio for all images? ! 
- [X] Push to Live
# 29 AUGUST 25
- [X] See also style.css RE: carousel stuff into Drink Plugin 
#28 AUGUST 25
- [x] Move Carousel stuff into Drinks Plugin (from Cocktail-Images). 
# 27 AUGUST 25
- [x] Understand how to extend blocks.  |  How to controls "Additional CSS Classes" with a custom toggle button?  |  Assess block code for attributes, not sidebar fields.  
- [x] And duplicate lightbox functionality
# 26 AUGUST 25
- [x] Split Image Modifications into Own plugin. 
# 25 AUGUST 25
- [x] Add button to Image Blocks, 
- [x] 'Nother button
# 23 AUGUST 25
- [x] Rebuild Springtime from Old templates, then backup Live  
- [x] Image Matching fails on Fireplace page (but OK on Everyday.)
# 20 AUGUST 25
 - [x]  Media Library Checker (images v. posts) pushed to live  
# 14 AUGUST 25
 - [x] Fix duplicate titles in existing posts? i.e. those before 29 July need manual review ??- JRA conf'd 
# 12 AUGUST 25 
 - [X]  Remove Drink-Post-Content Pattern's Title: Pushing this breaks everything =>
 - [x] Backup Live from yesterday so Media files will work? ~~Or Contact Support ~~ 
 - [X] Test Push- [X] Real Push 





# NOT PLANNED 

 ~~- [ ] enable units for wd/height of images in GUI | Not worth it.~~
~~- [ ] Remove-time suffix from category names (WHERE applicable?) ~~
~~- [ ] Ginger Cider Cosmo & Eggnog Martini broken pictures.~~ Unable to confirm. 
~~- [ ] broken Butterfly image ?~~ Unable to confirm. 
 ~~- [ ] Pattern of columns/Images to make page building faster ?~~
~~ - [ ]Can Media Library be alphabetized?~~
- ~~! Add Banner Category to photo titles in order to exlude from auto-swapping.~~
