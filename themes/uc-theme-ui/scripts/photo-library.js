
	  
	  
	  
	  
	  
	  

	  
	  
	  
	  
//////		Iterate through (count of photos) # elements and 
//////		ADD insert figcaption= <img title>	

/*Hook action to DOM content loaded(optional alternative)*/
//When HTML DOM Loads, addCaptions to HTML. 
addEventListener("DOMContentLoaded", addCaptionsToPage());

/*onDOMContentLoaded = (event) => {};)*/

function addCaptionsToPage(){
	
	//return # of images in a document
	var numPhotos= document.images.length; 
    //append to HTML
	console.log("Number of photos: " + numPhotos);
	
	
	//declare arrays by class type 
	//You can use Array.from(array-like) to concert and Array-like to an Array.
	let portraitArray = Array.from(document.getElementsByClassName("portrait")); 
	console.log("Portrait Photos: " + portraitArray.length);
	let landscapeArray=Array.from(document.getElementsByClassName("landscape"));
	console.log("Landscape Photos: " + landscapeArray.length);
	
	//Array of Figures
	let figureArray = portraitArray.concat(landscapeArray);
	console.log("Gallery Total: " + figureArray.length); ///GOOD TO Here
	
	//Array of images
	let imgArray=document.querySelectorAll("img");
	
	//first loop to add captions
	for (i=0; i<imgArray.length; i++){
		
		//declare working image element
		let img = imgArray[i];
		console.log(img);
		
		//turn display "on" for <figure> elements
		figureArray[i].style.display = "flex";
		figureArray[i].style.flexDirection = "column";
		
		//create caption element as variable
		let c = document.createElement("FIGCAPTION");
		//populate from image title (avoid innerHTML)
		c.innerText = img.title;  
	
		//make visible by adding to document
		//figureArray[i].append(c);		//THIS causes captions to offset intended image by two?
		imgArray[i].after(c);	//this works as expected
		console.log(c);
		
		
	}	
	
	
}   ///END FUNCTION addCaptionsToPage







//Need an addImage function


















































////////////////////////		CHANGE PHOTO AND caption by clicking
/*<button onclick="changefigure()">2011</button>
 <figure>
        <img id="Image" src="NT_Naplan_Reading_Results_2017.png"/>
        <figcaption id="caption"><em>Fig 2</em>Percent of children above national minimum standard in 
        reading in 2017 for Year 3, 5, 7 and 9 for Non-Indigenous and Indigenous children in the 
        Northern Territory. Data Source <a href="">NAPLAN results</a></figcaption>
    </figure>*/
function changefigure()
    {
        var x = document.getElementById("Image");
        var y = document.getElementById("caption");
        x.src = 'NT_Naplan_Reading_Results_2011.png';
        y.innerHTML = 'Your Caption Here';
    }
////////////////////////		CHANGE PHOTO AND caption by clicking







/*////////////		FILTERING EXAMPLE		 /////////////////////
/////////////		FILTERING EXAMPLE		 /////////////////////
/////////////		FILTERING EXAMPLE		 /////////////////////
filterSelection("all") // Execute the function and show all columns
function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName("column");
  if (c == "all") c = "";
  // Add the "show" class (display:block) to the filtered elements, and remove the "show" class from the elements that are not selected
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], "show");
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
  }
}

// Show filtered elements
function w3AddClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) { 
      element.className += " " + arr2[i];
    }
  }
}

// Hide elements that are not selected
function w3RemoveClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);
    }
  }
  element.className = arr1.join(" ");
}

// Add active class to the current button (highlight it)
var btnContainer = document.getElementById("myBtnContainer");
var btns = btnContainer.getElementsByClassName("btn");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function(){
    var current = document.getElementsByClassName("active");
    current[0].className = current[0].className.replace(" active", "");
    this.className += " active";
  });
}

/////////////		FILTERING EXAMPLE		 /////////////////////
/////////////		FILTERING EXAMPLE		 /////////////////////
/////////////		FILTERING EXAMPLE		 ////////////////////*/