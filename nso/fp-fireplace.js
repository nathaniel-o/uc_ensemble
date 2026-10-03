
/*		////	GLOBAL VARIABLES	////		*/
/*		////	GLOBAL VARIABLES	////		*/

/*	Drink Object Definition	*/
//FORMERLY const Drink = function pourDrink
class Drink{
	constructor(source, cocktail, pagecode, color, glass, garnish1, garnish2, garnish3, base, ice){

		this.src = source;
		this.cocktail = cocktail;
		this.pageCode = pagecode;
		this.color = color;
		this.glass = glass;
		this.garnish1 = garnish1;
		this.garnish2 = garnish2;
		this.garnish3 = garnish3;
		base: base;
		ice: ice; 
		//this.onclick = "showMeThatCocktail()"; //tested not the solution



const SAFETY_COPY_OF_POPOUT_EVTLISTENER_FAIL = {
			//	function showMeThisCocktail(e){
			
			//			let detailsPane;
				//	switch(document.querySelector('.pop-off')){
							
						
				//		case 'null':
							/*store parent <figure> of clicked <img> (or <figcaption>)*/
			//				const cetteBoisson = e.target.parentElement;
							/*re-style figure*/
			//				cetteBoisson.style.borderRadius = "0";
			////				cetteBoisson.style.border = "none"; /*"thick ridge rgba(222, 222, 222, 1.0)";*/
						
							//console.log(cetteBoisson.childNodes);
			////				const detailCaption = cetteBoisson.children[1];
			////				detailCaption.removeAttribute('id');
			////				detailCaption.id = 'detailCaption';
							//console.log(detailCaption);	//success
			
							/*create Element & Variable for .pop-off pane*/
			//				detailsPane=document.createElement('DIV');
									
							/*add this <figure> to pop-out pane*/
			//				detailsPane.appendChild(cetteBoisson);
			
					/*CONTINUE filling detailsPane*/
							/*create subheads' container*/
			//				const subheads = document.createElement('div');
			//			subheads.classList.add('subheads');
							/*create subheads*/
			//				const subHeadOne= document.createElement('a');
							//a.setAttribute('href',desiredLink);
			//				subHeadOne.innerHTML = 'desiredText'; 
			
			//				var subHeadTwo = document.createElement('a');
							//a.setAttribute('href',desiredLink);
			//				subHeadTwo.innerHTML = 'desirableText';
							
			//				detailsPane.appendChild(subHeadOne);
			//				detailsPane.appendChild(subHeadTwo);
			
							/*Apply styles from .pop-off, make visible*/
			//				detailsPane.classList.add('pop-off');
							
							/*THESE were attempts to make pop-out UNIFORM size*/
							//detailsPane.classList.remove('portrait');
									//detailsPane.classList.remove('landscape');
								
									
					//RESUME HERE//				
									/*ALLOW OVERLAY .image-gallery as background
									(turn OFF display: none*/
			//						detailsPane.style.display = "flex"; 
			
									//console.log(detailsPane.childNodes);
			//						detailsPane.style.borderRadius = "0"; //
									
								
			//						document.querySelector('.image-gallery').style.filter = "blur(2px)";
			//						document.body.prepend(detailsPane);
					//	break; /*End Case One*/
			
			
						/*Else when pop-off != null*/
			//			case 'true': 
			//				detailsPane = document.querySelector('.pop-off');
			
			//				detailsPane.style.display = "none";
			//			}/*End switch/case*/
			
			
					/* HTML TEMPLACE TO BE DYNAMIC
					<div class="subheads">
									<a href="fireplace.html">Fireplace</a>
									<a>Bold & Brash</a>
								</div>
									
									
								<div class = "tagged">	
									<ul class = "col1">
										<li>cranberry</li>
										<li>brandy</li>
										<li>cognac</li>
										<li>citrus</li>
									</ul>
									<ul class= "col2">
										<li>orange</li>
										<li>tornado</li>
										<li>GranMarnier</li>
										<li>lemon</li>
									</ul>
								</div>
					HTML TEMPLATE TO BE DYNAMIC*/
			
			
									
					//return detailsPane;}}
			
				} /*END showMeThatCocktail SAFETY COPY*/ 
			
			}
}/*	End Drink Object	SAFETY_COPY_OF_POPOUT_EVTLISTENER_FAIL*/
	




/*Array of Drink Objects*/
const DRINK_ARRAY = new Array();

/*Array of <figures>*/
const galleryDiv = Array.from(makeDrinks()[0].children);
//console.log(galleryDiv);

/*		////	END GLOBAL VARIABLES	////		*/
/*		////	END GLOBAL VARIABLES	////		*/










/* Event Listener Works ! to POP OUT when function() isn't 
...showMeThatCocktail() ANSWER WHY YOU FOOL?!?! */    
var figures = document.querySelectorAll('figure');
    for (let figure of figures) {
        figure.addEventListener("click", function(e){

			let detailsPane;
	//	switch(document.querySelector('.pop-off')){
				
			
	//		case 'null':
				/*store parent <figure> of clicked <img> (or <figcaption>)*/
				const cetteBoisson = e.target.parentElement;
				/*re-style figure*/
				cetteBoisson.style.borderRadius = "0";
//				cetteBoisson.style.border = "none"; /*"thick ridge rgba(222, 222, 222, 1.0)";*/
			
				//console.log(cetteBoisson.childNodes);
//				const detailCaption = cetteBoisson.children[1];
//				detailCaption.removeAttribute('id');
//				detailCaption.id = 'detailCaption';
				//console.log(detailCaption);	//success

				/*create Element & Variable for .pop-off pane*/
				detailsPane=document.createElement('DIV');
						
				/*add this <figure> to pop-out pane*/
				detailsPane.appendChild(cetteBoisson);

		/*CONTINUE filling detailsPane*/
				/*create subheads' container*/
				const subheads = document.createElement('div');
			subheads.classList.add('subheads');
				/*create subheads*/
				const subHeadOne= document.createElement('a');
				//a.setAttribute('href',desiredLink);
				subHeadOne.innerHTML = 'desiredText'; 

				var subHeadTwo = document.createElement('a');
				//a.setAttribute('href',desiredLink);
				subHeadTwo.innerHTML = 'desirableText';
				
				detailsPane.appendChild(subHeadOne);
				detailsPane.appendChild(subHeadTwo);

				/*Apply styles from .pop-off, make visible*/
				detailsPane.classList.add('pop-off');
				
				/*THESE were attempts to make pop-out UNIFORM size*/
				//detailsPane.classList.remove('portrait');
						//detailsPane.classList.remove('landscape');
					
						
		//RESUME HERE//				
						/*ALLOW OVERLAY .image-gallery as background
						(turn OFF display: none*/
						detailsPane.style.display = "flex"; 

						//console.log(detailsPane.childNodes);
						detailsPane.style.borderRadius = "0"; //
						
					
						document.querySelector('.image-gallery').style.filter = "blur(2px)";
						document.body.prepend(detailsPane);
		//	break; /*End Case One*/


			/*Else when pop-off != null*/
//			case 'true': 
//				detailsPane = document.querySelector('.pop-off');

//				detailsPane.style.display = "none";
//			}/*End switch/case*/


		/* HTML TEMPLACE TO BE DYNAMIC
		<div class="subheads">
						<a href="fireplace.html">Fireplace</a>
						<a>Bold & Brash</a>
					</div>
						
						
					<div class = "tagged">	
						<ul class = "col1">
							<li>cranberry</li>
							<li>brandy</li>
							<li>cognac</li>
							<li>citrus</li>
						</ul>
						<ul class= "col2">
							<li>orange</li>
							<li>tornado</li>
							<li>GranMarnier</li>
							<li>lemon</li>
						</ul>
					</div>
		HTML TEMPLATE TO BE DYNAMIC*/


						
		return detailsPane;}); 
    }
/* Event Listener Works ! to POP OUT when function() isn't 
...showMeThatCocktail() ANSWER WHY YOU FOOL?!?! */ 



	


		
document.addEventListener('DOMContentLoaded', makeDrinks());


/* CONSTRUCT <table> to DRINK_ARRAY (Objects) & galleryDiv (<figures>)    */
function makeDrinks(){

	/*to be returned*/
	let galleryDiv;
 
	/*mess of data output from spreadsheet*/
	let sourceTable = document.getElementById("source-table");

	/*peel the onion*/
	let details = Array.from(sourceTable.children);

	/*isolate OUR ACTUAL DATA*/
	let body = details[1];

	/*torso represents Array of all <tr> ROWS*/
	let torso = Array.from(body.querySelectorAll("tr"));



	/* Iterate through <tr> row elements, torso[i]*/
	for(let i = 1; i< torso.length; i++){ 


		/*Count the number of elements in this (i) row*/
		const c = torso[i].childElementCount;
		//console.log("This row has " + c + " elements.");

		/*Store row as an array variable (ess. a Drink) */
		let row = Array.from(torso[i].children);
		//console.log(row);

		/*Start new Drink (by iterating through a row to add properties*/
		/*let DRINK_ARRAY = [];        made this GLOBAL 09/02 (see top) 	*/
		DRINK_ARRAY[i] = new Drink();

		/*Print Object from Array
		console.log(DRINK_ARRAY[i]); */

		/*Print diagnostic PlaceHolder
		console.log("Row Zero:");*/

		/*theLink needs Scope*/
		let theLink;
		

		/* IF anchor tag exists, extract the URL Link, "href" attribute */
		if (row[0].querySelector("a") !== null){
			let arms = row[0].querySelector("a");
			//console.log(arms);
			theLink = arms.getAttribute("href");
			//console.log(theLink);

		}

		
		
	


		/*create parent <figure>*/
		const figure = document.createElement("FIGURE");
		/*create new <img>*/
		const drinkImage = new Image();
		/*set <img src= property from DRINK_ARRAY = local variable*/
		drinkImage.src = DRINK_ARRAY[i].src = theLink;
		/*add <img> to <figure>, then SHOW*/
		figure.appendChild(drinkImage);
		figure.style = "display:flex";

		/*apply picture sizing*/
		if(drinkImage.height > drinkImage.width){
			figure.classList.add("portrait");
		} else {
			figure.classList.add("landscape");
		}

		/*create galleryDiv variable from <div "">*/
		galleryDiv = document.getElementsByClassName("image-gallery");

		/*Assign caption to <img alt="" & title=""   */
		DRINK_ARRAY[i].cocktail = drinkImage.alt = drinkImage.title = row[1].innerText;
		/*Create <figcaption*/
		const figcaption = document.createElement("FIGCAPTION");
		figcaption.innerText = drinkImage.title;

		/*SCROLLING EXPERIMENT*/
		figure.id = "scroll-container";
		figcaption.id = "scroll-text";
		

		/*Append caption to Figure, whole to image-gallery w/ HTMLObject index[0]*/
		figure.appendChild(figcaption);
		galleryDiv[0].appendChild(figure);
	

		//Chris Jada Sareda Michelle Erin, Roger Carlos Jessica LEASING


		/*Add the <figure> to HTML (body)*/
		//document.body.appendChild(figure)


		/*CONSTRUCT Drink[i] from row[index] of <table>*/
		DRINK_ARRAY[i].color = row[2].innerText;
		DRINK_ARRAY[i].glass = row[3].innerText;
		DRINK_ARRAY[i].pageCode = row[4].innerText;
		DRINK_ARRAY[i].garnish1 = row[5].innerText;
		DRINK_ARRAY[i].garnish2 = row[6].innerText;
		DRINK_ARRAY[i].garnish3 = row[7].innerText;
		DRINK_ARRAY[i].base = row[8].innerText;
		DRINK_ARRAY[i].ice = row[9].innerText;

		/*Print Drink info*/
		//console.log(DRINK_ARRAY[i]);

		/*Iterate through each <td>  by row=torso[i]*/
		for(let j = 0; j < c ; j++){
			/*Print each row's data, by column*/
			//console.log(row[j].innerText);
		}


		/*ADD AN IMAGE to document
				const myImage = new Image(100, 200);
				myImage.src = "picture.jpg";
				document.body.appendChild(myImage);  */

		
	}


	//console.log('galleryDiv');
			//console.log(document.getElementsByClassName("image-gallery"));
	//		console.log(galleryDiv[0]);
			//console.log(Array.from(galleryDiv[0]));

	
	//return DRINK_ARRAY; /*	CAUSES: "Uncaught TypeError: Property 'handleEvent' is not callable."	*/
return galleryDiv;  /*NECESSARY for makeDrinks()[0].children in global assignment*/} 
/*			END makeDrinks() 		*/
/*	END CONSTRUCTOR makeDrinks()	*/













/*	////	////	USEFUL EXAMPLES		////	////	////	*/
/*	////	////	USEFUL EXAMPLES		////	////	////	*/


//EXAMPLE FROM VIDEO    https://www.youtube.com/watch?v=QghhoJBdw7A
/*
<container><image-container><figure=image>
<class="popup-image"><span>$times


<script>
document.querySelectorAll('.image-container img').forEach(image =>{
	image.onclick = ( ) => {
		document.querySelector('.popup-image').style.display=block;
		document.querySelector('.popup-image img).src = image.getAttribute('src');
}});
	document.querySelector('.popup-image span').onclick = () => {
		 document.querySelector('.popup-image').style.display=none;
	}

*/
//END EXAMPLE





/*EXAMPLE  Listener reports title attribute of whichever element clicked
document.addEventListener('click', function(evt) {
    alert(evt.target.title);
}, false);     */
//END EXAMPLE







/*		EXAMPLE To Insert before, rather than appendChild()		*/
/*var eElement; // some E DOM instance
var newFirstElement; //element which should be first in E

eElement.insertBefore(newFirstElement, eElement.firstChild);	*/
/*		END EXAMPLE To Insert before, rather than appendChild()	*/



/*	////	////	USEFUL EXAMPLES		////	////	////	*/
/*	////	////	USEFUL EXAMPLES		////	////	////	*/









/* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
function myFunction() {
	var x = document.getElementById("tierOne");
	if (x.style.display === "block") {
	  x.style.display = "none";
	} else {
	  x.style.display = "block";
	}
  }