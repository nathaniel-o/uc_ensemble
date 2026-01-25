<?php
/**
 * Title: UC Media & Text Old
 * Slug: uc-media-text-old
 * Categories: Media
*/
?>




<link rel="stylesheet" href="style.css"> 

<?php echo '
<figure class = "pop-off" id = "pane"> 
    <a href="http://localhost/wordpress/wp-content/uploads/2024/12/Cranberry-sidecar_FP-T-2-scaled.jpg" 
        target="_parent">
        <span style="color:blue; display: none;">http://localhost/wordpress/wp-content/uploads/2024/12/Cranberry-sidecar_FP-T-2-scaled.jpg</span>
        <img src ="http://localhost/wordpress/wp-content/uploads/2024/12/Cranberry-sidecar_FP-T-2-scaled.jpg" 
            alt = "Cranberry Sidecar"
            title = "Cranberry Sidecar"/>
        <figcaption  id="detailCaption"><u>Cranberry Sidecar</u></figcaption>
    </a>
                        
    <!--div class="subheads">
        <a href="fireplace.html">Fireplace</a>
        <a>Bold & Brash</a>
    </div-->m

                    
                    
    <div class = "tagged">	
        <ul class = "col1">
            <a><li>cranberry</li></a>
            <a><li>brandy</li></a>
            <a><li>cognac</li></a>
            <a><li>citrus</li></a>
        </ul>
        <ul class= "col2">
            <a><li>orange</li></a>
            <a><li>tornado</li></a>
            <a><li>GranMarnier</li></a> 
            <a><li>lemon</li></a>
        </ul>
    </div>

    <button class = "close-button">X</button>
</figure>';?>




<style>
    @media (orientation: portrait) {
        .pop-off {
            width: 90vw;
            max-height: 90vh;
        }
        
        .pop-off img {
            max-height: 65vh;  /* Increased from 60vh to allow more image space */
            width: 100%;
            object-fit: contain;
        }
        
        .tagged {
            flex-direction: column;
        }
        
        #detailCaption {
            font-size: 1.8rem;  /* Reduced from 2.4rem */
        }
    }

    @media (orientation: landscape) {
        .pop-off {
            max-width: 55vw;  /* Reduced from 65vw to be closer to img width */
            max-height: 95vh;  /* Increased slightly to use more viewport space */
            padding: 8px;  /* Reduced padding to save space */
        }
        
        .pop-off img {
            max-height: 65vh;  /* Reduced from 75vh to leave room for other content */
            width: 100%;
            object-fit: contain;
        }
        
        .tagged {
            flex-direction: column;
            padding: 8px;  /* Reduced padding */
            gap: 12px;  /* Reduced gap */
        }
        
        #detailCaption {
            font-size: 1.6rem;  /* Further reduced */
            margin: 4px 0;
        }

        .tagged li {
            font-size: 1.2rem;  /* Further reduced */
            line-height: 1.2;
        }
    }

    @media all {
        .pop-off {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed;  /* Changed from absolute for better viewport control */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.9);
            border: 1.2em ridge var(--details-clr-bone-white);
            border-radius: 8px;
            padding: 12px;
            z-index: 5;
            overflow: auto;  /* Allow scrolling if content overflows */
        }

        .tagged {
            width: 100%;
            display: flex;
            justify-content: center;  /* Changed from space-between */
            background-color: rgba(255, 243, 224, 0.95);
            border-radius: 8px;
            padding: 12px;  /* Reduced from 16px */
            margin-top: 8px;
            gap: 20px;  /* Add space between stacked columns */
        }

        .tagged ul {
            margin: 0;
            padding: 0 12px;  /* Reduced from 20px */
            list-style: none;
        }

        .tagged li {
            font-size: 1.4rem;  /* Reduced from 1.8rem */
            line-height: 1.4;  /* Reduced from 1.6 */
            font-family: "Gill Sans MT", sans-serif;
            text-align: center;
            color: rgb(36,21,71);
        }

        #detailCaption {
            color: #b8b5ff;
            margin: 6px 0;  /* Reduced from 8px */
            padding: 3px;  /* Reduced from 4px */
            text-align: center;
        }
    }
</style>
 