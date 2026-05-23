<?php
/**
 * Title: Horizontal Timeline
 * Slug: uc-theme-ui/timeplate
 * Categories: featured, layout
 * Block Types: core/group, core/columns
 *
 * Horizontal timeline pattern
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* {
  box-sizing: border-box;
}

body {
  background-color: #474e5d;
  font-family: Helvetica, sans-serif;
  --container-height: 100px;
}

.timeline-wrapper {
  border: 4px solid cyan;
  padding: 0;
  margin: 20px auto;
  max-width: 1240px;
}

/* The actual timeline (the vertical ruler) */
.timeline {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
}

/* The actual timeline (the vertical ruler) */
.timeline::after {
  content: '';
  position: absolute;
  width: 6px;
  background-color: white;
  top: 0;
  bottom: 0;
  left: 50%;
  margin-left: -3px;
}

/* Horizontal line-only variant */
.timeline--horizontal-line-only {
  position: relative;
  max-width: 1200px;
  margin: 0;
  /* apparently you can declare variables at first use */
  height: calc(2 *  var(--container-height, 100px) + 300px);
}

.timeline--horizontal-line-only::after {
  content: '';
  position: absolute;
  height: 6px;
  background-color: white;
  top: 50%;
  left: 0;
  right: 0;
  width: auto;
  bottom: auto;
  margin-left: 0;
}

/* Entries for the horizontal-only timeline */
.timeline--horizontal-line-only .container {
  position: absolute;
  width: 40%;
  min-height: var(--container-height);
}

.timeline--horizontal-line-only .left {
  top: 10%;
  left: 10%;
}

.timeline--horizontal-line-only .right {
  bottom: 10%;
  right: 10%;
}

/* Circle at the connection point to the center line */
.timeline--horizontal-line-only .container::after {
  content: '';
  position: absolute;
  width: 25px;
  height: 25px;
  background-color: white;
  border: 4px solid #FF9F55;
  border-radius: 50%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2;
}

.timeline--horizontal-line-only .left::after {
  bottom: -4px;
}

.timeline--horizontal-line-only .right::after {
  top: -4px;
}

/* Pointer arrows aiming toward the center line */
.timeline--horizontal-line-only .left::before,
.timeline--horizontal-line-only .right::before {
  content: " ";
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  z-index: 2;
  border-style: solid;
}

.timeline--horizontal-line-only .left::before {
  bottom: -10px;
  border-width: 10px 10px 0 10px;
  border-color: white transparent transparent transparent;
}

.timeline--horizontal-line-only .right::before {
  top: -10px;
  border-width: 0 10px 10px 10px;
  border-color: transparent transparent white transparent;
}

/* Container around content */
.container {
  padding: 10px 40px;
  position: relative;
  background-color: inherit;
  width: 50%;
}

/* The circles on the timeline */
.container::after {
  content: '';
  position: absolute;
  width: 25px;
  height: 25px;
  right: -17px;
  background-color: white;
  border: 4px solid #FF9F55;
  border-radius: 50%;
  z-index: 1;
}

/* Place the container to the left */
.left {
  left: 0;
}

/* Place the container to the right */
.right {
  left: 50%;
}

/* Add arrows to the left container (pointing right) */
.left::before {
  content: " ";
  height: 0;
  position: absolute;
  width: 0;
  z-index: 1;
  right: 30px;
  border: medium solid white;
  border-width: 10px 0 10px 10px;
  border-color: transparent transparent transparent white;
}

/* Add arrows to the right container (pointing left) */
.right::before {
  content: " ";
  height: 0;
  position: absolute;
  width: 0;
  z-index: 1;
  left: 30px;
  border: medium solid white;
  border-width: 10px 10px 10px 0;
  border-color: transparent white transparent transparent;
}

/* Vertical timelines only: position arrows at center line */
.timeline:not(.timeline--horizontal-line-only) .left::before,
.timeline:not(.timeline--horizontal-line-only) .right::before {
  top: 22px;
}

/* Vertical timelines only: position circles at center line */
.timeline:not(.timeline--horizontal-line-only) .container::after {
  top: 15px;
}

/* Fix the circle for containers on the right side */
.right::after {
  left: -16px;
}

/* The actual content */
.content {
  padding: 20px 30px;
  background-color: white;
  position: relative;
  border-radius: 6px;
}

/* Media queries - Responsive timeline on screens less than 600px wide */
@media screen and (max-width: 600px) {
  /* Place the timelime to the left */
  .timeline::after {
  left: 31px;
  }
  
  /* Full-width containers */
  .container {
  width: 100%;
  padding-left: 70px;
  padding-right: 25px;
  }
  
  /* Make sure that all arrows are pointing leftwards */
  .container::before {
  left: 60px;
  border: medium solid white;
  border-width: 10px 10px 10px 0;
  border-color: transparent white transparent transparent;
  }

  /* Make sure all circles are at the same spot */
  .left::after, .right::after {
  left: 15px;
  }
  
  /* Make all right containers behave like the left ones */
  .right {
  left: 0%;
  }
}
</style>
</head>
<body>

<div class="timeline-wrapper">
  <div class="timeline">
    <div class="container left">
      <div class="content">
        <h2>2017</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2016</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container left">
      <div class="content">
        <h2>2015</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2012</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container left">
      <div class="content">
        <h2>2011</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2007</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
  </div>
</div>

<div class="timeline-wrapper">
  <div class="timeline timeline--horizontal-line-only">
    <div class="container left">
      <div class="content">
        <h2>Above the line</h2>
        <p>This entry sits above the horizontal center line.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>Below the line</h2>
        <p>This entry sits below the horizontal center line.</p>
      </div>
    </div>
  </div>
</div>

<div class="timeline-wrapper">
  <div class="timeline">
    <div class="container left">
      <div class="content">
        <h2>2017</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2016</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container left">
      <div class="content">
        <h2>2015</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2012</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container left">
      <div class="content">
        <h2>2011</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
    <div class="container right">
      <div class="content">
        <h2>2007</h2>
        <p>Lorem ipsum dolor sit amet, quo ei simul congue exerci, ad nec admodum perfecto mnesarchum, vim ea mazim fierent detracto. Ea quis iuvaret expetendis his, te elit voluptua dignissim per, habeo iusto primis ea eam.</p>
      </div>
    </div>
  </div>
</div>

</body>
</html>