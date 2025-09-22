/* 
*
*    AN BACKUP OF functions.js on 17 Dec 2024, after which gallery ought be of WordPress compatible 
*
*/

class Drink{
	constructor(){
	/*	constructor needs no Params	??
		source, cocktail, pageCode, color, glass, garnish1, garnish2, garnish3, base, ice	*/
		this.src = `source`;
		this.cocktail = `cocktail`;
		this.pageCode = `pageCode`;
		this.color = `color`;
		this.glass = `glass`;
		this.garnish1 = `garnish1`;
		this.garnish2 = `garnish2`;
		this.garnish3 = `garnish3`; 
		this.base = `base`;
		this.ice = `ice`; 

		
	}	


	//Turn this drink to a figure w/ caption
	ucConvertDrinkToFigure(){	//	accept Drink Object (read from self), returns <figure> 

		var anImage = '<img src="' + this.src + 
						'" alt="' + this.cocktail  + 
						'"title="' + this.cocktail + 
						'"  />';
		//console.log(anImage);
		var anCaption = '<figcaption id="scroll-text">' + this.cocktail + '</figcaption>';
		//console.log(anCaption);

		//var anFigure = '<figure id="scroll-container">' + anImage + anCaption +  '</figure>';
		let anFigure = document.createElement("figure");
		anFigure.id = "scroll-container";
		anFigure.innerHTML = anImage + anCaption; 
		
		

			// Image orientation detection now handled by drinks-plugin
			//console.log("IMAGES ASSIGNED");

		
		return anFigure;
		
	}

	/*	
	Convert Drink to pop-off classed Modal type
	Accepts Boolean preview where T limits details in modal to 3 at Random
	*/
	ucShowDetails(preview){	
		const detailsPane = document.createElement("FIGURE");
		/*create new <img>*/
		const drinkImage = new Image();
		/*set <img src*/
		drinkImage.src = this.src;
		/*Assign caption to <img alt="" & title=""   */
		drinkImage.alt = drinkImage.title = this.cocktail;
			
		/*add <img> to <figure>*/
		detailsPane.appendChild(drinkImage);

		/*Create <figcaption & append*/
		const figcaption = document.createElement("FIGCAPTION");
		figcaption.innerText = drinkImage.title;
		detailsPane.appendChild(figcaption);

		/* 	SUBHEADS NO LONGER DESIRED	
		const subHeads = document.createElement("DIV");
			const subHeadOne = document.createElement("A");
			subHeadOne.href = "fireplace.html";
			subHeadOne.innerText = this.pageCode;
			const subHeadTwo = document.createElement("A");
			subHeadTwo.href = "";
			subHeadTwo.innerText = this.garnish1;
		subHeads.appendChild(subHeadOne);
		subHeads.appendChild(subHeadTwo);		subHeads.classList.add("subheads");		
												detailsPane.appendChild(subHeads);		*/

		const categories = Object.keys(this);
		categories.shift(); 	/*exclude src*/

		//if(categories.includes("tags")){
			/*(SIDE EFFECT) of ucMatchDrinks : exclude last entry Drink.tags=[]  */
		//	categories.pop();

		//}
		/*rename value pairs */
		categories[1] = "Category";
		categories[2] = "Color";
		categories[3] = "Glass"; 
		categories[4] = "Garnish";
		categories[5] = "Garnish 2";
		categories[6] = "Garnish 3";
		categories[7] = "Base";
		categories[8] = "Ice";
		//console.log(categories); console.log("names above");

		const tags = Object.values(this); /*perfect*/
		tags.shift(); //exclude src
		//console.log(tags); console.log("tagged");
			
		const tagsDiv = document.createElement("DIV");
		tagsDiv.classList.add("tagged");

			let ucTerm;
			 
			for(let i = 1; i < categories.length; i++){  //  For properties, skipping the first(name)...


				
				const tag = document.createElement("P");  //  Create a <p>
				
				if(categories[i] === "Category"){    //  Format Category Key where value is format "RO - Romantic"

					tags[i] = ucRemovePrefix(tags[i]);	/*success	*/

				}


				
				/* 		*/
				if(categories[i].includes("Garnish")){
					
					categories[i] = "Garnish";  //  Remove "2" & "3"



					if(tags[i].includes("Fruit:")){

						//console.log(tags[i]);
						tags[i] = tags[i].slice(7);


					}

					
				}
				

				if(tags[i] !== "placeholder" ){		/*excellent //&& categories[i] !== "Garnish 2" && categories[i] !=="Garnish 3"*/	


					let thisTag = tags[i].charAt(0).toUpperCase() + tags[i].slice(1);
					//console.log(temp);
					tag.innerText = categories[i] + " : " + thisTag;

					tagsDiv.appendChild(tag); 
					//columnOne.appendChild(tag);
					//console.log(tag.innerText);
				
				}
				//console.log(tag); /*show empty where placeholder exists*/

				ucTerm = document.querySelector("#ucQuery").value;
				//console.log(ucTerm);
				//IF Search term has value 
				if (ucTerm !== " " && ucTerm != ""  && tag.innerText.toString().toLowerCase().includes(ucTerm.toString().toLowerCase())){


					//THEN give lighter font 
					tag.classList.add("searchTerm");
				}  //  Positive side effect that applies to all until search term has some value 

				
				
			}  // END for 			

			//  If preview for Carousel, 
				if(preview){					

					for(let i = tagsDiv.children.length; i > 3 ; i--){
						let j = getRandomInt(tagsDiv.children.length);

						// TRYING TO BE SURE SEARCH TERM IS SHOWN 
						//console.log(tagsDiv.children[j].innerHTML.includes(ucTerm));

						//  IF search box is not empty, and term matches
						if( ucTerm != "" && ucTerm != " " && tagsDiv.children[j].innerHTML.includes(ucTerm) == true){
							
							//Do nothing, leave the element alone (to be shown) 

						} else {
							tagsDiv.children[j].remove(); 

						}
					}
					//console.log("END");
					//console.log(tagsDiv);
					
					
				} 
			


			//    Assemble the pieces
			//tagsDiv.appendChild(columnOne);
			//tagsDiv.appendChild(columnTwo);
			detailsPane.appendChild(tagsDiv);


	

		


		/*apply css*/
		detailsPane.classList.add("pop-off");
		/*STOP scrolling text due broken z-index	*/
		document.querySelectorAll("#scroll-text").forEach(element => {
			element.removeAttribute("ID");
		});
		
			return detailsPane;

		}    //end else
}	


/*    CORE FUNCTIONS : TURN DATA TO DRINKS    
   			 ////    ////    ////    ////    */

/* ucMakeDrinks
	GETS source-table HTML from page
	CALLS ucArrayOfTableRows (CALLS ucConvertRowToDrink)
	MODIFIES DRINK_ARRAY by copying in drinkArr
	RETURNS drinkArr ?
	*/
	function ucMakeDrinks(){

		// GET HTML source data as <table> 
		const drinkMenu = document.querySelector("#source-table");

		//console.log(drinkMenu);

		const tableRows = ucArrayofTableRows(drinkMenu);
		//console.log(tableRows);
		

		const drinkArr = [];	
		for(let i = 0 ; i < tableRows.length ; i++){

			//console.log(i);
			drinkArr.push(ucConvertRowToDrink(tableRows[i]));
			
			//console.log(drinkArr[i]);
		}

		let i =0;
				drinkArr.forEach((element) => {

					DRINK_ARRAY.push(drinkArr[i]);

					i++;
				})

		return drinkArr;
	}
	function ucArrayofTableRows(lmntName){
		//console.log(lmntName);
			
		const temp = Array.from(lmntName.children[0].children);
		
		return temp;   
	}
	function ucConvertRowToDrink(drinkData){	/*input should be tableRows[index], output Drink Object*/
		drinkData = Array.from(drinkData.children);
	 
		const drinkEntry = new Drink();
		//console.log(Object.entries(drinkEntry));	
		let i = 0;
		//console.log(drinkEntry);
		//let j = Object.keys(drinkEntry).length;
		for (property in drinkEntry){		
				//console.log(i);
				//console.log(drinkData[i]);
				//console.log(drinkData);
				
				//console.log(i);
				//console.log(drinkData[i]);
			
			if(drinkData[i] !== undefined && drinkData[i] !== null){	
				drinkEntry[property] = drinkData[i].innerText;
			}//console.log(`${property}: ${drinkEntry[property]}`);
			i++;
			
			//console.log(drinkEntry.src);
								 }


		

		return drinkEntry; 
	} 

        ////    ////    ////    ////
/*    CORE FUNCTIONS : TURN DATA TO DRINKS    */





		/*    calculate height where :auto won't work    */
		function galleryHeight(){  
										

			//console.log("HEIGHT TEST");
			let thesePhotos  = Array.from(document.querySelectorAll(".portrait, .landscape"));
			//console.log(thesePhotos);
			let heightSum = 0;
			thesePhotos.forEach((figure) => {
				
				heightSum += figure.children[0].height; 
				//console.log(heightSum);

			});
			
			let pageHeight = (heightSum * 1.52) / 2; 
			//console.log(pageHeight);

			

			let pageEl = document.getElementById("springtimeHTML");
			
			//console.log(pageEl);
			/*   must add units to cast to CSS    */
			pageEl.style.height = pageHeight + 'px';

			
}






		/*    MISC. HELPER FUNCTIONS    
		    ////    ////    ////    ////    */ 
			
			// Image orientation detection moved to drinks-plugin
			// Function ucPortraitLandscape has been refactored and moved to the drinks plugin
	/*
		Remove 3 digits of anString 
		modifies original string
		*/
	function ucRemovePrefix(anString){ 
			/*ACCEPTS tags[i] in .pop-off, column constructing for loop above*/

			let ucStr = Array.from(anString);
			//console.log(ucStr);
			//console.log("Arr");

			/*quick fix*/ 
			ucStr.shift();
			ucStr.shift(); ucStr.shift();

			
			/*	join() excludes commas from array, unlike .toString()	*/
			anString = ucStr.join("");
			//console.log(anString);
			//console.log("final");



			return anString;
	}

	/*	an Show / Hide toggle for elements within the navbar    */
	function myFunction() {
	/*IF WE MUST, compare height of #size-this to height
	of img (which controls overall container bounds*/

		/*    The List of Page Links    */  
		var tierOne = document.getElementById("tierOne");

		/*    Stand- in when tierOne is hidden. I.e. Contact Info    */
		var y = document.getElementsByClassName("write-over")[0];

		var z = document.querySelector(".topnav figure");
			
		var hamburger = document.querySelector(".hamburger");
				
			if (tierOne.style.display === "block") {
				tierOne.style.display = "none";
				y.style.display = "block";
				z.classList.add("icon");
				z.classList.remove("icoff");
				hamburger.classList.remove("icoff");
				/*w.style.height = "12.5%;";*/     
			} else{
				tierOne.style.display = "block";
				y.style.display="none";
				z.classList.remove("icon");
				z.classList.add("icoff");
				hamburger.classList.add("icoff");
			
				
			/*w.style.display="12.5%";*/
			}
			
			//console.log("W");
			//console.log(w);
				
				
				
			/*MAKE DYNAMIC*/
			ucRemoveMenuItem();
	}
		
	/*    ucRemoveMenuItem filters current page off navbar...	*/
	function ucRemoveMenuItem(){
		
			var thisPage = document.getElementsByTagName("title")[0].innerText;
			//console.log(thisPage);
			var thesePages = document.getElementById("tierOne");
			thesePages = Array.from(thesePages.children);
			
			for (let i = 0; i < thesePages.length; i++){
				
				let currentPage =  thesePages[i].innerText;
				//console.log(currentPage);
		
				/* FIXED BELOW if(currentPage == thisPage){ */
				if(thisPage.includes(currentPage)){   
					
					thesePages[i].setAttribute("id", "hidden"); /*EFFECTIVE*/
					//console.log("IF Succeeded");
					/*console.log(thesePages[i]);*/
			
		
				}
			}
			//console.log(thisPage);
			//console.log(thesePages);
		
	}


	/*  Accepts .querySelector type DOM item, 
	*   Returns height in px of tallest child item, Recursion style
	*   Called By welcome-carousel.php pattern <script insert 
	*/
	function findTallestChild(node) {
	
	 
		let maxHeight = 0;
	  
		function traverse(node) {
		  const childHeight = node.offsetHeight; // or node.clientHeight
		  if (childHeight > maxHeight) maxHeight = childHeight;
	  
		  if (node.children.length === 0) return maxHeight; // leaf node, return height
		  /* node.children.forEach was not a function (NOT due queryselector, not sure why  */
		  Array.from(node.children).forEach((child) => traverse(child));
		}
	  
	
			traverse(node);
			return maxHeight;

		
	}  /* END findTallestChild */



	////    ////    ////    //// 
/*    MISC. HELPER FUNCTIONS    */







/* DOCUMENT FUNCTIONS : Manipulate the HTML
////    ////    ////    ////    ////    */

//Build Gallery based on Page ID ( for Tier One ) 
function ucBuildGallery(anTargetContainer, anSearchTerm){


	//IF Page is Gallery, then build gallery of all photos
	if(	pageID.includes("gallery") === true){

		for (Drink of DRINK_ARRAY){

			ucGalleryInsert(setCSSByPage(Drink.ucConvertDrinkToFigure()), anTargetContainer);
					
		}

	}
	//console.log(anSearchTerm == null);
	
	//IF search term argument was passed, then build gallery of matching Drinks
	if( anSearchTerm !== null && anSearchTerm !== undefined){

			//anSearchTerm = anSearchTerm.split("-")[0];
			if(anSearchTerm.includes("-") == true){
				anSearchTerm = anSearchTerm.replace("-", " ");
			}

			let resultsArr = ucMatchDrinks(anSearchTerm);
			//console.log(resultsArr);

			for(Drink of resultsArr){
				ucGalleryInsert(setCSSByPage(Drink.ucConvertDrinkToFigure()), anTargetContainer);
				//console.log(Drink.src);
			}
		//console.log(anSearchTerm);

	
	}



	// #scroll-container ID must remain even where #scroll-caption is not in use
	ucListenIteratively(Array.from(document.querySelectorAll("#scroll-container")));	



}


//Equivalent grouping function as BuildGallery 
function ucFillCarousel(anSearchTerm, numResults){

	let results = ucMatchDrinks(anSearchTerm);  //Do a Search 
	

	for(let i = 1; i <= numResults; i++){       //Restrict number of ucGalleryInserts to # of desired imgs in carousel

		let j = getRandomInt(results.length);    //randomize the image inserted, within the search terms

		//console.log(i);
		//console.log(results[j]); 

		let containerString = "carousel__slide" + i;
		//console.log(containerString);
		
		
		//INSERT DETAILS to carousel slide
		//ucGalleryInsert(results[j].ucShowDetails(), containerString);

		//INSERT FIGURE to carousel slide
		//console.log(results[j].ucShowDetails(true)); //returns Drink correctly? 
		ucGalleryInsert(results[j].ucShowDetails(true), containerString); 
		//console.log("TEST");
		
		
	} // END for 

	let carousel = document.querySelector(".carousel");
	//  Show the Carousel if previously Hidden
	if(carousel.classList.contains("hidden")){
		carousel.classList.remove("hidden");
	}
	carousel.classList.add("angled-gradient");
	//console.log(Array.from(document.querySelectorAll(".carousel__snapper figure")));
	let carouselFigs = Array.from(document.querySelectorAll(".carousel__snapper figure"));
	carouselFigs.forEach((figure) =>{

		setCSSByPage(figure);
	//	console.log(figure);

	});

	//  Blue the body, exlude where NOT "image-gallery"
	let b = document.getElementsByClassName("image-gallery")[0];
	if( b !== null && b !== undefined ){
		b.classList.add("is-blurred");
	}
	ucCloseBtn("carousel", "inside");


	
}

function ucIndexCarousel(indexDrinks){

for(let i = 1; i <= indexDrinks.length; i++){       //Restrict number of ucGalleryInserts to # of desired imgs in carousel

	


	console.log(indexDrinks[i]);

	//let containerString = "carousel__slide" + i;

	//INSERT FIGURE to carousel slide
	//console.log(results[j].ucShowDetails(true)); //returns Drink correctly? 
	//ucGalleryInsert(indexDrinks[i].ucShowDetails(true), containerString); 
	//console.log("TEST");
	
	
} // END for 

let carousel = document.querySelector(".carousel");
//  Show the Carousel if previously Hidden
if(carousel.classList.contains("hidden")){
	carousel.classList.remove("hidden");
}
carousel.classList.add("angled-gradient");


//console.log(Array.from(document.querySelectorAll(".carousel__snapper figure")));
let carouselFigs = Array.from(document.querySelectorAll(".carousel__snapper figure"));
carouselFigs.forEach((figure) =>{

	setCSSByPage(figure);
//	console.log(figure);

});

//  Blue the body, exlude where NOT "image-gallery"
let b = document.getElementsByClassName("image-gallery")[0];
if( b !== null && b !== undefined ){
	b.classList.add("is-blurred");
}
ucCloseBtn("carousel", "inside");



}




//  Create a close Btn with Evt Lstnr to hide parent container (whether pop-off or carousel)
//  posit designates inside/outside top-R corner
function ucCloseBtn(anClassName, posit){
//  Get choice Parent Element
const div= document.getElementsByClassName(anClassName)[0];
//console.log(div);

//  Initialize an "X" button
const closeBtn = document.createElement("BUTTON");
	closeBtn.innerText = "X";
	closeBtn.classList.add("close-button");
//  Place button based on function call	
closeBtn.classList.add(posit);


console.log(document.querySelector(".posit"));

div.appendChild(closeBtn);
//console.log(div);

if(anClassName == "carousel"){   // IF Closing the Carousel...
	closeBtn.addEventListener("click", (e) => {
		//  Hide Carousel
		e.target.parentElement.classList.add("hidden"); 
		//  Un-Blur the image-gallery			
		document.getElementsByClassName("image-gallery")[0].classList.remove("is-blurred");

		console.log(e.target);
		
		e.target.remove(); // no dice  why carousel then pop-off missing close button?
		


	}); 
}
else{ //ELSE (if closing the details pane)
	closeBtn.addEventListener("click", (e) => {
		console.log(e.target.parentElement);
		e.target.parentElement.remove();
		//e.target.parentElement.classList.add("hidden"); 
			 
	});
}

}





//FIVE: insert <element> to <image-gallery>, return same figure 		
//		(use return value for diagnostic console only!) 
function ucGalleryInsert(anHTMLFigure, anTargetContainer){		
	/*retrieve galleryDiv as variable from <div "">*/

	//console.log(targetContainer[0]);

	//console.log(targetContainer);
	

	let targetContainer;

	// Prepend directly to target container for most cases
	if(anTargetContainer.includes("carousel") === false){
	
		targetContainer = document.getElementsByClassName(anTargetContainer);
		targetContainer = targetContainer[0];
		
	}
	//For Carousel, query slide # by ID since unique
	else if (anTargetContainer.includes("carousel") === true){

		targetContainer = document.getElementById(anTargetContainer).children[0];
		//console.log(targetContainer);
		//targetContainer = anTargetContainer;
	}
	//For all, add HTML figure to targetContainer
	targetContainer.prepend(anHTMLFigure);

	//console.log(targetContainer);

}


	function setCSSByPage(anFigure){
		/*
			if(pageID.includes("gallery")){
				anFigure.classList.add("gallery");
				
			} else if (pageID.includes("special")){
				anFigure.classList.add("special");
			} else {}
		*/
			//	console.log("BEFORE");
			//	console.log(anFigure);
			//	console.log(pageID);
	if(pageID.length > 0){		        //CONTINUE HERE
				anFigure.classList.add(pageID);  //pageID is Empty on Home page ? 
	}
				//console.log(pageID.includes("gallery") == false);
			//	console.log("AFTER");
			//	console.log(anFigure);
		
				
		
				/*
				Remove scrolling text on all except Gallery Page
				*/
				if (pageID.includes("gallery") == false){
				//	console.log(anFigure);
					anFigure.children[1].removeAttribute("ID");

				}

				//console.log(anFigure);


			return anFigure;
	}


   ////    ////    ////    ////    ////
/* DOCUMENT FUNCTIONS : Manipulate the HTML    */










/*    SEARCH & FILTER FUNCTIONS    
////    ////    ////    ////    */
							

//	SEVENT POINT FIVE:	Apply click Event Listener to an <figure Array]'s img & caption elements 
function ucListenIteratively(anyFigureArray){

for(const figure of anyFigureArray){
	const nodes = figure.childNodes;
	nodes.forEach(element => {
	//console.log(element);
			element.addEventListener("click", 
				(e) => {	
					/*	Show clicked Drink (as Object	*/
					//console.log( /*ucCreateModalFromDrink(*/ ucMatchSelection(e.target) );
					//console.log(e.target.parentElement);
				
					//console.log("FROM HERE");
					//console.log( ucMatchSelection(e.target));


					let anFig = ucMatchSelection(e.target).ucShowDetails();
					/*	Show Modal by initializing with class="pop-off"	*/
					ucGalleryInsert( anFig, "image-gallery");

					ucCloseBtn("pop-off", "outside");


				
				})	}
				);	};
			
return 1; /*Is it possible to return the modified array, such that function
				inserts Clickable Gallery? */
}	


//	EIGHT:  Accept clicked e.target, return Drink Object	
function ucMatchSelection(clickTarget){
//console.log(clickTarget.parentElement.children[0].src); //OKAY
//	console.log("TEST");
//	console.log(clickTarget.parentElement.children[0].src);
//let i = 0;
for(Drink of DRINK_ARRAY){		
	//console.log(i);
	//console.log(Drink.src);	
	if(Drink.src === clickTarget.parentElement.children[0].src){		
		//console.log(Drink);  console.log("DrinkSource MATCH");
		
		return Drink; //returns Successful
		}  	
	else { //console.log("FAILURE"); 
		}
	
 }    return 1;	//return OUTSIDE for loop assures iteration
}


//    accepts a string, returns Array of Drinks w/ that string    
function ucMatchDrinks(anString){

//console.log(anString);

let ucResults = [];
let searchTerm = anString.toLowerCase();
//let j = 0;/*count iterations only*/

/*    for each Drink...    */
for(Drink of DRINK_ARRAY){
//	console.log(j);/*count iterations only*/

/*    create new searchable tags Array property (include all property key:values as strings    */
let tags  = Object.keys(Drink).concat(Object.values(Drink));
//console.log(Drink.tags);

/*    for each elements in tags...    */
for(let i = 0; i < tags.length ; i ++){

		/*    Convert element b/c Obj v. Str. matters ?    */
		let tagEntry = tags[i].toString();


		

		/*    IF tag matches && result Arr doesn't include Drink...    */
		if(tagEntry.toLowerCase().includes(searchTerm) && ucResults.includes(Drink) === false){
			//console.log("match");
			//console.log(ucResults.length);
			
			ucResults.push(Drink);
			

		//console.log("RESULTS");
		//console.log(ucResults);
				
		}    //		j++; /*count iterations only*/
	}
}


const theseResults =  ucResults; //Better Don't reassign Arrays,

//console.log(theseResults);

return theseResults;
}


//  Empty container on document of all its <figures>
//  ::::::: Re-populate the container with a search

function ucEmptyContainer(anTargetContainer){


//BREAK HERE due populating is more important
if(anTargetContainer === "carousel"){

	let carousel = document.querySelector("." + anTargetContainer);

	//console.log(carousel.classList);
	if(carousel.classList.contains("hidden")){ /* RESUME HERE TO MAKE carousel toggle as the Details Pane does */
		carousel.removeAttribute("hidden");
	}

	let slides = Array.from(carousel.children[0].children);
	
	
	slides.forEach((element) => {


		//console.log(element.children[0].children[0] !== undefined); //undefined on first click, populates anyway
		
		if(element.children[0].children[0] !== undefined){
			element.children[0].children[0].remove();
			//let e = element.children[0].children[0];
			//e.remove();
		}

//				if( element.classList.contains("hidden")){
//					element.classList.remove("hidden");
//					return 0; //returns false if item is not hidden by method
//				} else {
//					element.classList.add("hidden"); 
//					return 1;
//				}

		

	});

	
	
	
	/*
	REUSE BELOW TO ACTUALLY REMOVE <FIGURES>?  
	*/

	//Isolate Carousel's <li> where figures reside
	//let disappear = Array.from(document.querySelectorAll("."+anTargetContainer)[0].children[0].children);

	//disappear.forEach((node) => {
//		node.classList.add("hidden");
	//});


} else { //Else, assume container is Gallery

	let disappear = document.querySelectorAll("."+anTargetContainer+" figure");

	
	for(let i = 0; i < disappear.length; i++){
		disappear[i].id = "hidden";
		//console.log(disappear[i]);
	}
	//console.log("HIDDEN");
	return 1;
}

}

function getRandomInt(max) {
return Math.floor(Math.random() * max);
}

function ucRegisterSearchBox(){

/* register search box */
const ucSearchBox = document.getElementById('ucQuery');
//console.log(ucSearchBox);
//console.log(document.querySelector("#ucQuery"));
//console.log(ucSearchBox); 
/*    for each key in box... (e not in use)    */
ucSearchBox.addEventListener("keyup", (e) => {
	

	console.log(ucMatchDrinks(e.target.value));  //Returns Drink Array


	ucEmptyContainer("image-gallery");
	ucBuildGallery("image-gallery", e.target.value);


} );




/*    trigger same on button press... (e not in use)   */
let searchBtn = document.querySelector(".testBtn");
searchBtn.addEventListener("click", (e) => {

	const term = document.getElementById("ucQuery").value;
	//console.log(term.value);
	

	//ucEmptyContainer("image-gallery");

	//ucBuildGallery("image-gallery", e.target.value);
	ucEmptyContainer("carousel"); 

	
	
	ucFillCarousel(term, 3);

			

});
}

   ////    ////    ////    ////
/*    SEARCH & FILTER FUNCTIONS    */






/* 
*    Remove the silly 2 line SVG from WP header, 
*    instead use our hamburger (insert by CSS) 
*/
function ucCustomizeWPHeader(){

var oldBurger = document.querySelector("body > div.wp-site-blocks > header > nav > button > svg");

oldBurger.remove();




}

function ucAddPaginationLeftArrowToCarousel(){
// Find all Query blocks with carousel style
const carousels = document.querySelectorAll('.is-style-carousel.wp-block-query');

if(carousels){
	carousels.forEach(carousel => {
		// Check if pagination container exists, if not create it
		let paginationContainer = carousel.querySelector('.wp-block-query-pagination');
		if (!paginationContainer) {
			paginationContainer = document.createElement('div');
			paginationContainer.className = 'wp-block-query-pagination';
			carousel.appendChild(paginationContainer);
		}
		
		// Create and insert previous button if it doesn't exist
		if (!carousel.querySelector('.wp-block-query-pagination-previous')) {
			const prevButton = document.createElement('a');
			prevButton.href = '#last-post';
			prevButton.className = 'wp-block-query-pagination-previous';
			prevButton.setAttribute('data-slide', 'last');
			prevButton.textContent = 'Previous Page';
			
			// Insert at the start of pagination container
			paginationContainer.insertBefore(prevButton, paginationContainer.firstChild);
		}
	});
}
}








const DRINK_ARRAY = new Array();

// Enhanced styling function copied from functions.js with category detection
function styleImagesByPageID(variableID, targetContainer) {
	
	if(!targetContainer){
		targetContainer = '.entry-content';
	}

	// Get all images within target container
	const imageContainer = document.querySelector(targetContainer);
	if (!imageContainer) {    //  If no target, no action. 
		return;
	}

	const images = imageContainer.querySelectorAll('img');

	images.forEach(img => {
		// Extract category code from image title/alt
		const categoryCode = extractCategoryFromImage(img);
		let currentVariableID = variableID;
		
		// Override variableID with category-based styling if category detected
		if (categoryCode) {
			currentVariableID = mapCategoryCodeToVariable(categoryCode);
		}

		if(currentVariableID.includes("springtime")){
			currentVariableID = "summertime";
		}  //  (Else currentVariableID = currentVariableID as passed)

		// Compose variable names
		const borderVar = `var(--${currentVariableID}-border)`;
		const fontColorVar = `var(--${currentVariableID}-font-color)`;
		const shadowVar = `var(--${currentVariableID}-shadow)`;

		// 1. Apply border variable
		img.style.border = borderVar;

		// 2 & 3. If image is in a figure with figcaption, style the caption
		const figure = img.closest('figure');
		if (figure) {
			const caption = figure.querySelector('figcaption');
			if (caption) {
				caption.style.color = fontColorVar;
				caption.style.textShadow = shadowVar;
			}
		}
	});
}

// Function to extract category code from image title/alt
function extractCategoryFromImage(img) {
	const title = img.title || img.alt || '';
	
	// Look for pattern like "_AU_T" or "_RO_T" in the title
	const categoryMatch = title.match(/_([A-Z]{2})_/);
	if (categoryMatch) {
		return categoryMatch[1]; // Returns "AU", "RO", etc.
	}
	
	return null;
}

// Function to map category codes to variable names
function mapCategoryCodeToVariable(categoryCode) {
	const categoryMap = {
		'AU': 'autumnal',
		'RO': 'romantic', 
		'EV': 'everyday',
		'SU': 'summertime',
		'SP': 'summertime', // springtime maps to summertime
		'FP': 'fireplace',
		'SO': 'special-occasion',
		'WI': 'winter'
	};
	
	return categoryMap[categoryCode] || 'std';
}

// Function to style pop-out dynamically based on drink category
function ucStylePopoutDynamic(){
	// Get all pop-out elements
	const popoutBodies = document.querySelectorAll('.drinks-lightbox-body, .drinks-popout-body, .drinks-content-popout, .pop-off');
	
	popoutBodies.forEach(popoutBody => {
		// Find ul elements within the pop-out
		const ulElements = popoutBody.querySelectorAll('ul');
		
		ulElements.forEach(ul => {
			// Find the image in this pop-out to determine category
			const img = popoutBody.querySelector('img');
			let categoryVariable = 'std';
			
			if (img) {
				const categoryCode = extractCategoryFromImage(img);
				if (categoryCode) {
					categoryVariable = mapCategoryCodeToVariable(categoryCode);
				}
			}
			
			// Style the ul with the appropriate font color
			ul.style.color = `var(--${categoryVariable}-font-color)`;
			
			// Style em elements within the ul to be black
			const emElements = ul.querySelectorAll('em');
			emElements.forEach(em => {
				em.style.color = "black";
				em.style.fontWeight = "bold";
				em.style.fontStyle = "normal";
				em.style.marginRight = "0.25em";
			});
		});
	});
}

// Function to style carousel slides per drink category
function ucStyleCarouselSlides() {
	const carouselSlides = document.querySelectorAll('.carousel__slide, .carousel__snapper figure');
	
	carouselSlides.forEach(slide => {
		const img = slide.querySelector('img');
		if (img) {
			const categoryCode = extractCategoryFromImage(img);
			if (categoryCode) {
				const categoryVariable = mapCategoryCodeToVariable(categoryCode);
				
				// Apply styling to the slide
				styleImagesByPageID(categoryVariable, slide);
				
				// Style any ul elements in the slide
				const ulElements = slide.querySelectorAll('ul');
				ulElements.forEach(ul => {
					ul.style.color = `var(--${categoryVariable}-font-color)`;
					
					const emElements = ul.querySelectorAll('em');
					emElements.forEach(em => {
						em.style.color = "black";
						em.style.fontWeight = "bold";
						em.style.fontStyle = "normal";
						em.style.marginRight = "0.25em";
					});
				});
			}
		}
	});
}

/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */
////    ////    ////    ////
////    ////    ////    ////
/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */

/*		...WHEN PAGE LOADS... 		*/
/* document.addEventListener("DOMContentLoaded", (event) =>{ */
window.addEventListener("load", (event) =>{
	//console.log("Resources Loaded");
	
	//ucRemoveMenuItem(); // TEMP REMOVE DUE ERRORS

	/*  ON EVERY PAGE...  */

		//ucCustomizeWPHeader();
	//	ucRegisterSearchBox();
	const drinkArr = ucMakeDrinks(); //modifies (generates) DRINK_ARRAY
//	console.log(drinkArr);
		
	
	//IF Page is Gallery, 
	if(pageID.includes("gallery")===true){
		//Insert all photos by omit second arg
		ucBuildGallery("image-gallery");	
	} 

	if(pageID.includes("contact")===true){
		//TRYING to prevent auto page refresh

		//Get form element
		var form=document.getElementById("contact-form");
		//console.log(form);

		function submitForm(event){
		
		//Preventing page refresh
	//	event.preventDefault();
		}

	
		//Calling a function during form submission.
		form.addEventListener('submit', submitForm);
	


	}

if(pageID.includes("home")===true){
	let indexArray = [`<figure class = "home-figure landscape">
		<img  src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Pair-of-Fireplace-Drinks_FP-T-scaled.jpg" 
				alt = "Pair of Fireplace Drinks" 
				title = "Pair of Fireplace Drinks" />
		<figcaption>Fireplace Cocktails</figcaption>
		</figure>`,
	
		
	`<figure class = "home-figure portrait" id="foto-deux">
		<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Ginger-Peach-n-Scotch_AU-T-rotated.jpg" 
			alt = "Ginger Peach n' Scotch Autumnal Cocktail"
			title = "Ginger Peach n' Scotch Autumnal Cocktail" />
		<figcaption>Seasonal Cocktails</figcaption>
	</figure>`,
	

`<figure class = "home-figure portrait">
	<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Holiday-Old-Fashioned-and-Kir-Royale_SO-T.jpg" 
		alt = "Holiday Old Fashioned and Kir Royale"
		title = "Holiday Old Fashioned and Kir Royale" />
	<figcaption>Special Occasion Cocktails</figcaption>
</figure>`,
	
		
		
	
		
`<figure class = "home-figure portrait" id="foto-quatre">
	<img src ="http://untouchedcocktails.com/wp-content/uploads/2024/09/Citrus-Gimlet-with-Butterfly-Pea-Flower-Float_EV-T.jpg" 
		alt = "Citrus Gimlet with Butterfly Pea Flower Float Everyday Cocktail" 
		title = "Citrus Gimlet with Butterfly Pea Flower Float Everyday Cocktail" />
	<figcaption>Everyday Cocktails</figcaption>
</figure>`,
			

		`<figure class = "home-figure portrait" id="foto-last">
		<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Cranberry-Citrus-Dragon_RO-T.jpg"
			alt = "Cranberry Citrus Dragon Romantic Cocktail"
			title = "Cranberry Citrus Dragon Romantic Cocktail" />
		<figcaption>Romantic Cocktails</figcaption>
		</figure>    `  ]  ;
		
	

//console.log(indexArray);
ucIndexCarousel(indexArray);
}
	

	//IF page is not Gallery, index, or contact, 
	if(pageID.includes("contact")===false &&
	pageID.includes("home")===false &&
	pageID.includes("gallery")===false)  {

		let pageCode = pageID.split("-")[0];
		//Then Build Gallery filtered by first word
		ucBuildGallery("image-gallery", pageCode);

		//console.log(ucMatchDrinks("kir royale"));

	}




	//  resize once on page load...    
	if(document.title ==="Springtime Cocktails"){
		console.log("RESIZED");
		galleryHeight();

	}
	
});



/*  ...then resize on resize.    */
window.addEventListener("resize", (event) => {
	
	if(document.title ==="Springtime Cocktails"){
		//console.log("RESIZED");
		galleryHeight();

	}
	});


	document.addEventListener('DOMContentLoaded', 
		
		function() {
		// Find all Query blocks with carousel style
		const carousels = document.querySelectorAll('.is-style-carousel.wp-block-query');
		
		carousels.forEach(carousel => {
			// Check if pagination container exists, if not create it
			let paginationContainer = carousel.querySelector('.wp-block-query-pagination');
			if (!paginationContainer) {
				paginationContainer = document.createElement('div');
				paginationContainer.className = 'wp-block-query-pagination';
				carousel.appendChild(paginationContainer);
			}
			
			// Create and insert previous button if it doesn't exist
			if (!carousel.querySelector('.wp-block-query-pagination-previous')) {
				const prevButton = document.createElement('a');
				prevButton.href = '#last-post';
				prevButton.className = 'wp-block-query-pagination-previous';
				prevButton.setAttribute('data-slide', 'last');
				prevButton.textContent = 'Previous Page';
				
				// Insert at the start of pagination container
				paginationContainer.insertBefore(prevButton, paginationContainer.firstChild);
			}
		});





		
			if(pageID.includes("home")===true){
				const indexArray = [
					`<figure class = "home-figure landscape">
						<img  src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Pair-of-Fireplace-Drinks_FP-T-scaled.jpg" 
							alt = "Pair of Fireplace Drinks" 
							title = "Pair of Fireplace Drinks" />
						<figcaption>Fireplace Cocktails</figcaption>
					</figure>`,
			
				
					`<figure class = "home-figure portrait" id="foto-deux">
						<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Ginger-Peach-n-Scotch_AU-T-rotated.jpg" 
							alt = "Ginger Peach n' Scotch Autumnal Cocktail"
							title = "Ginger Peach n' Scotch Autumnal Cocktail" />
						<figcaption>Seasonal Cocktails</figcaption>
					</figure>`,
			
					`<figure class = "home-figure portrait">
						<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Holiday-Old-Fashioned-and-Kir-Royale_SO-T.jpg" 
							alt = "Holiday Old Fashioned and Kir Royale"
				title = "Holiday Old Fashioned and Kir Royale" />
			<figcaption>Special Occasion Cocktails</figcaption>
					</figure>`,
				
					`<figure class = "home-figure portrait" id="foto-quatre">
						<img src ="http://untouchedcocktails.com/wp-content/uploads/2024/09/Citrus-Gimlet-with-Butterfly-Pea-Flower-Float_EV-T.jpg" 
							alt = "Citrus Gimlet with Butterfly Pea Flower Float Everyday Cocktail" 
					title = "Citrus Gimlet with Butterfly Pea Flower Float Everyday Cocktail" />
							<figcaption>Everyday Cocktails</figcaption>
					</figure>`,
						
					`<figure class = "home-figure portrait" id="foto-last">
					<img src = "http://untouchedcocktails.com/wp-content/uploads/2024/09/Cranberry-Citrus-Dragon_RO-T.jpg"
						alt = "Cranberry Citrus Dragon Romantic Cocktail"
						title = "Cranberry Citrus Dragon Romantic Cocktail" />
					<figcaption>Romantic Cocktails</figcaption>
					</figure>    `  ]  ;
				
			}
					

	}

);


/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */
////    ////    ////    ////
////    ////    ////    ////
/*    ACTIONS : WHERE THE FUNCTIONS ARE CALLED     */





	//	EIGHT:  Accept clicked e.target, return Drink Object	
	function ucMatchSelection(clickTarget){
		//console.log(clickTarget.parentElement.children[0].src); //OKAY
	//	console.log("TEST");
	//	console.log(clickTarget.parentElement.children[0].src);
		//let i = 0;
		for(Drink of DRINK_ARRAY){		
			//console.log(i);
			//console.log(Drink.src);	
			if(Drink.src === clickTarget.parentElement.children[0].src){		
				//console.log(Drink);  console.log("DrinkSource MATCH");
				
				return Drink; //returns Successful
				}  	
			else { //console.log("FAILURE"); 
				}
			
		 }    return 1;	//return OUTSIDE for loop assures iteration
	}


	//    accepts a string, returns Array of Drinks w/ that string    
	function ucMatchDrinks(anString){
	
	//console.log(anString);

	let ucResults = [];
	let searchTerm = anString.toLowerCase();
	//let j = 0;/*count iterations only*/

	/*    for each Drink...    */
	for(Drink of DRINK_ARRAY){
		//	console.log(j);/*count iterations only*/

		/*    create new searchable tags Array property (include all property key:values as strings    */
		let tags  = Object.keys(Drink).concat(Object.values(Drink));
		//console.log(Drink.tags);
	
		/*    for each elements in tags...    */
		for(let i = 0; i < tags.length ; i ++){

				/*    Convert element b/c Obj v. Str. matters ?    */
				let tagEntry = tags[i].toString();


				

				/*    IF tag matches && result Arr doesn't include Drink...    */
				if(tagEntry.toLowerCase().includes(searchTerm) && ucResults.includes(Drink) === false){
					//console.log("match");
					//console.log(ucResults.length);
					
					ucResults.push(Drink);
					

				//console.log("RESULTS");
				//console.log(ucResults);
						
				}    //		j++; /*count iterations only*/
			}
	}


	 const theseResults =  ucResults; //Better Don't reassign Arrays,

	 //console.log(theseResults);

	return theseResults;
    }

	

	function resizeCarousel(node){ //Resize all Carousels // Not in Use 

		const lookHere = document.querySelectorAll('.carousel, .is-style-carousel');
		console.log(lookHere);
		
		lookHere.forEach(carousel => {
			const carouselHeight = findTallestChild(carousel) * 1.1;     // terrible fix 
	
			carousel.style.height = carouselHeight  + 'px';   
			console.log(carousel.style.height);
			console.log(carouselHeight); 
	   
		});
	}

	
	function ucAddPaginationLeftArrowToCarousel(){
		// Find all Query blocks with carousel style
		const carousels = document.querySelectorAll('.is-style-carousel.wp-block-query');
		
		if(carousels){
			carousels.forEach(carousel => {
				// Check if pagination container exists, if not create it
				let paginationContainer = carousel.querySelector('.wp-block-query-pagination');
				if (!paginationContainer) {
					paginationContainer = document.createElement('div');
					paginationContainer.className = 'wp-block-query-pagination';
					carousel.appendChild(paginationContainer);
				}
				
				// Create and insert previous button if it doesn't exist
				if (!carousel.querySelector('.wp-block-query-pagination-previous')) {
					const prevButton = document.createElement('a');
					prevButton.href = '#last-post';
					prevButton.className = 'wp-block-query-pagination-previous';
					prevButton.setAttribute('data-slide', 'last');
					prevButton.textContent = 'Previous Page';
					
					// Insert at the start of pagination container
					paginationContainer.insertBefore(prevButton, paginationContainer.firstChild);
				}
			});
		}
	}



