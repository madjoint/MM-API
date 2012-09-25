/*
Droppings.js

Small JS library, that will transform regular text documents into
ninja-styled black hawks with the possibility of drag'n'drop.
*/

Droppings = {
  dropboxes: [],
  current_dependency: 0,
  dependencies: [
    "jquery-1.4.2.min.js",
    "jquery-ui-1.8.2.custom.min.js",
    "droppings.css",
  ],
  
  initialize: function(dropboxes) {
    // Set dropboxes.
    Droppings.dropboxes = dropboxes
    // Include dependencies.
    Droppings.load_dependencies()
  },
  
  try_dependencies: function() {
    if ( Droppings.verify_dependencies() )
    {
      Droppings.prepare_dom()
    }
    else
    {
      return
    }
  },
  
  verify_dependencies: function() {
    if ( !jQuery ) return false
    if ( !jQuery.ui ) return false
    if ( !jQuery.ui.droppable ) return false
    if ( !jQuery.ui.draggable ) return false
    return true
  },
  
  prepare_dom: function() {
    // Setup words as dragable.
    $(".draggable").draggable({ revert: true, zindex: 999, stack: ".draggable" })
    // Build dropboxes.
    Droppings.build_dropboxes()
  },
  
  build_dropboxes: function(dropboxes) {
    // Setup dropboxes as droppable
    var dropbox_container = $("<div id='dropboxes'>")
    
    // Loop through boxes
    $.each(Droppings.dropboxes, function() {
      // Get vars from array.
      var name = this[0]
      var colour = this[1]
      var url = this[2]
    
      // Create dropbox header.
      var header = $("<h3>").html(name)
    
      // Create dropbox
      var dropbox = $("<div>").attr("id", name).css("border", "2px solid #" + Droppings.colours(colour)).css("background", "#000")
      dropbox.append(header)
    
      // Make it droppable.      
      dropbox.droppable({ over: function() {
                            $(this).css("background", $(this).css("borderColor"))
                          },
                          out: function() {
                            $(this).css("background", "#000")
                          },
                          drop: function(event, ui) {
                            var droppable = $(this)
                            droppable.css("background", "#000")
                            // Get payload
                            hidden_data = ui.draggable.find(".draggable_data")
                            if ( hidden_data.length == 0 )
                              data = ui.draggable.html()
                            else
                              data = hidden_data.html()
                            
                            $.ajax({
                              url: url,
                              data: {param: data},
                              success: function(returned_data) {
                                if (returned_data && returned_data != "")
                                  ui.draggable.css("color", droppable.css("borderColor")).effect("pulsate", {times: 2})
                              }
                            })
                          }})

      // Append to container.
      dropbox_container.append(dropbox)
    })
  
    // Append to body.
    $("body").append(dropbox_container)
  },
  
  colours: function(clr) {
    clrs = []
    clrs["green"] = "A3FF67"
    clrs["yellow"] = "F8F757"
    clrs["red"] = "F86E68"
    clrs["blue"] = "55C8F8"
    
    if ( clrs[clr] )
      return clrs[clr]
    else
      return clr
  },

  next_dependency: function() {
    Droppings.current_dependency++
    if ( Droppings.current_dependency < Droppings.dependencies.length )
      Droppings.load_dependencies()
    Droppings.try_dependencies()
  },
  
  load_dependencies: function(filename) {
    var head_node = document.getElementsByTagName('HEAD')
    var dep = Droppings.dependencies[Droppings.current_dependency]
    // Get file extension.
    extension = dep.split(".").pop()
    
    if ( extension == "js" ) 
    { 
      var new_node = document.createElement('SCRIPT')
      new_node.type = 'text/javascript'
      new_node.src = dep

      if (head_node[0] != null)
        head_node[0].appendChild(new_node)
    }
    else if ( extension == "css" ) 
    {
      var new_node = document.createElement('LINK')
      new_node.type = 'text/css'
      new_node.rel = 'stylesheet'
      new_node.media = 'screen'
      new_node.href = dep

      if (head_node[0] != null)
        head_node[0].appendChild(new_node)
    }
    
    new_node.onreadystatechange = function () {
      if (this.readyState == 'complete' || this.readyState == 'loaded')
        Droppings.next_dependency()
    }
    new_node.onload = Droppings.next_dependency
  }
}