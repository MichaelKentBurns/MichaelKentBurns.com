/******************************************/
/* Generic Puzzle Menu     in Javascript **/
/* Copyright (c) 2022        Greg Grimes **/
/******************************************/
/*
** Menu
*/ GenVersion = '4.02'
/*
** Copyright    2022 - Greg Grimes
*/ Copyright = '2022 - Greg Grimes';
/*
** Bugs:
**   * ?
**
** To Do:
**   * Convert existing puzzles over to new paradigm
**   * Inherit those programs' To Do lists
**   * Perhaps naming conventions to avoid collisions
**   * Add an action to dump the entire page as HTML
**     both before and after puzzle embellishment
**   * Standards for throwing away arrays of HTML and
**     building all pieces from DOM objects
**   * Standard for defining entries in the SETTINGS
**     table and including an abbreviated version in
**     the menu windows
**
** Versions:
**    4.01   Common files from old V1 puzzles
**    4.02   About title
*/


//**********************
//**********************
//**                  **
//**  Menu Functions  **
//**                  **
//**********************
//**********************
Version        = GenVersion
gameWindow     = 0
showMenuHtml   = 0
showGameHtml   = 0
tracemenu      = 0
noMenuCols     = 0
menunum        = 0
menulist       = 0
playOffset     = 600
gameWinHigh    = 900
gameWinWide    = 900
puzzleTitle    = "Generic"
puzzleName     = "Generic"
puzzleType     = " Puzzle"
gamelink       = new Array

/*
** Constants
*/
nbsp           = String.fromCharCode(160)
char2610       = String.fromCharCode(9744) // &#9744; == &#x2610; == empty square
char2611       = String.fromCharCode(9745) // &#9745; == &#x2611; == check mark in square
dNam           = new Array ( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat")
mNam           = new Array ( "Jan", "Feb", "Mar",
                             "Apr", "May", "Jun",
                             "Jul", "Aug", "Sep",
                             "Oct", "Nov", "Dec" )

/*
** Menu of Puzzles to solve
*/
function buildMenu()
{
   var i
   var yy
   var mm
   var dd
   var checkDate = new Date
   var dn
   var day
   var mon

   /*
   ** Determine the appropriate number of columns in the table of game links.
   */
   menunum = menulist.length
   if ( debugMode && traceMenu )
     { alert("menunum is "+menunum) }
   if (menunum < 25)         //   1 - 25 rows
     { noMenuCols = 1 }
   else if (menunum < 60)    //  13 - 30 rows
     { noMenuCols = 2 }
   else if (menunum < 105)   //  20 - 35 rows
     { noMenuCols = 3 }
   else if (menunum < 160)   //  27 - 40 rows
     { noMenuCols = 4 }
   else if (menunum < 225)   //  32 - 45 rows
     { noMenuCols = 5 }
   else if (menunum < 271)   //  38 - 45 rows
     { noMenuCols = 6 }
   else if (menunum < 316)   //  39 - 45 rows
     { noMenuCols = 7 }
   else if (menunum < 361)   //  40 - 45 rows
     { noMenuCols = 8 }
   else // (menunum < 406)   //  41 - 45 rows
     { noMenuCols = 9 }
/* alert("menunum is "+menunum+" and noMenuCols is "+noMenuCols) */

   /*
   ** Compute the names for each game link.
   */
   for( i=0; i<menunum; i++ )
     {
      if ( (menulist[i].substring(2,3) == '0') ||
           (menulist[i].substring(2,3) == '1') ||
           (menulist[i].substring(2,3) == '2') ||
           (menulist[i].substring(2,3) == '3') ||
           (menulist[i].substring(2,3) == '4') ||
           (menulist[i].substring(2,3) == '5') )
        {
         yy = menulist[i].substring(2,4)
         mm = menulist[i].substring(4,6)
         dd = menulist[i].substring(6,8)
         checkDate.setFullYear(2000 + (yy-0))
         checkDate.setDate(dd-0)
         checkDate.setMonth(mm-1)
         dn = checkDate.getDay()
         day = dNam[dn]
         mon = mNam[mm-1]
         gamelink[i] = mon + " " + dd + ", 20" + yy + " (" + day + ")"
//       oldDstr = "Date:"+checkDate+"("+dn+"="+day+")"
//       tryDate = new Date(2000+(yy-0),mm,dd)
//       dn2 = tryDate.getDay()
//       da2 = dNam[dn2]
//       newDstr = "Try:"+tryDate+"("+dn2+"="+da2+")"
//       alert(oldDstr+" / "+newDstr)
        }
      else
        {
//             gamelink[i] = menulist[i] + puzzleType
         eval("gamelink[i] = menuItem"+puzzleName+"(menulist[i])")
        }
      /* Indicate we don't yet have a window for this game */
/**** playOffsets[i] = -1 ****/
     }
   showMenu()
}

function showMenu()
{
   var page
   var ullist
   var splits
   var collen
   var ulnum
   var nextspl
   var i
   var an
   var li
   var ul
   var href
   var title
   var clist

   page =
      '<!DOCTYPE html>'                                         + "\n" +
      '<html lang="en">'                                        + "\n" +
      '<head>'                                                  + "\n" +
      '<meta charset="utf-8"/>'                                 + "\n" +
      '<title>' + puzzleTitle + puzzleType + ' Menu</title>'    + "\n" +
      '</head>'                                                 + "\n" +
      '<body>'                                                  + "\n" +
      '<h1>'    + puzzleTitle + puzzleType + ' Menu</h1>'       + "\n" +
      '<table border="0">'                                      + "\n" +
      '<tr>'                                                    + "\n"

   for( i=0; i<noMenuCols; i++ )
     {
      page +=
         '<td valign=top>'                                      + "\n" +
         '<ul>'                                                 + "\n" +
         '</ul>'                                                + "\n" +
         '</td>'                                                + "\n"
     }

   page +=
      '</tr>'                                                   + "\n" +
      '</table>'                                                + "\n" +
      '</body>'                                                 + "\n" +
      '</html>'                                                 + "\n"

   if (showMenuHtml)
   {
      page = page.replace(/&/g,"&amp;")
      page = page.replace(/</g,"&lt;")
      page = page.replace(/>/g,"&gt;")

      page =
         '<!DOCTYPE html>'                                      + "\n" +
         '<html lang="en">'                                     + "\n" +
         '<head>'                                               + "\n" +
         '<meta charset="utf-8"/>'                              + "\n" +
         '<title>' + puzzleTitle + ' Menu HTML</title>'         + "\n" +
         '</head>'                                              + "\n" +
         '<body>'                                               + "\n" +
         '<h1>' + puzzleTitle + ' Menu HTML</h1>'               + "\n" +
         '<pre>'                                                + "\n" +
         page                                                          +
         '</pre>'                                               + "\n" +
         '</body>'                                              + "\n" +
         '</html>'                                              + "\n"
   }

   document.write(page)

   if (showMenuHtml)
   {
      /*
      ** If we are just showing the menu HTML,
      ** there are no DOM objects to manipulate.
      */
      return
   }

   /*
   ** The UL menu list is built with 1 LI entry.
   ** The element structure is:
   **   "UL" element has one child
   **   (1) "LI" element has three children
   **         (2) "A" element has one child
   **             (1) "#text" element for A
   **                 value:
   **         (3) "#text" element for LI
   **             value:
   */
   ullist  = document.getElementsByTagName("ul")
   splits  = ullist.length
   collen  = Math.ceil( menunum / splits )
   if ( debugMode && traceMenu )
     { alert("There are " + splits + " UL element(s).") }
   ulnum   = 0
   nextspl = 0
   for( i=0; i<menunum; i++ )
     {
      if ( i >= nextspl )
        {
         if ( debugMode && traceMenu )
           { alert("Splitting menu to the next column (i="+i+").") }
         ul      = ullist.item(ulnum)
         ulnum++
         if ( debugMode && traceMenu )
           { alert("The name of the UL element is '" + ul.nodeName + "'.") }
         nextspl = nextspl + collen
        }
      /* Create the LI node */
      li = document.createElement("li")
      if ( debugMode && traceMenu )
        { alert("The name of the LI element is '" + li.nodeName + "'.") }

      /* Create the A node */
      an = document.createElement("a")
      if ( debugMode && traceMenu )
        { alert("The name of the A element is '" + an.nodeName + "'.") }
      href = "javascript:play" + puzzleName + "('" + menulist[i] + "')"
      title = "Use file " + menulist[i] + ".js"
      an.setAttribute('href',href)
      an.setAttribute('title',title)

      /* Create the #text node for A */
      at = document.createTextNode(gamelink[i])
      if ( debugMode && traceMenu )
        { alert("The text of the A element is '" + at.data + "'.") }

      /* Hook them all into the list */
      an.appendChild(at)
      li.appendChild(an)
      ul.appendChild(li)
      clist = ul.childNodes
      if ( debugMode && traceMenu )
        { alert("The UL element now has " + clist.length + " child(ren).") }
     }
}


/*
** Generic puzzle 4.01.htm
** puzzle solving web page
** as a JavaScript array
*/
gameHtml = new Array (
   '<!DOCTYPE html>',
   '<html lang="en">',
   '<head>',
   '<meta charset="utf-8"/>',
   '<title>' + puzzleTitle + puzzleType + '</title>',
   '<script language="JavaScript" type="text/javascript" src="' + puzzleName + '.js"></' + 'script>',
   '<script language="JavaScript" type="text/javascript" src="GAMEID.js"></' + 'script>',
   '</head>',
   '<body>',
   '<h1 id="BANNERH1">&nbsp;</h1>',
   '<h3 id="BANNERH3">&nbsp;</h3>',
   '<table border="0" id="PAGE">',
   '<tr>',
   '<td align="center" valign="center">',
   '',
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp,
   '<table border="1" cellpadding="5" id="BOARD">',
   '<tr>',
   '   <td align="center" name="BOARDCELL" width="20" onclick="clickCell(0)"><font size="5">11</font></td>',
   '   <td align="center" name="BOARDCELL" width="20" onclick="clickCell(1)"><font size="5">12</font></td>',
   '</tr>',
   '<tr>',
   '   <td align="center" name="BOARDCELL" width="20" onclick="clickCell(2)"><font size="5">21</font></td>',
   '   <td align="center" name="BOARDCELL" width="20" onclick="clickCell(3)"><font size="5">22</font></td>',
   '</tr>',
   '</table>',
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+
   nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp+nbsp,
   '<br>',
   '',
   '</td>',
   '<td width="25">&nbsp;</td>',
   '<td valign="top">',
   '',
   '<font size="4"><b id="menuTitle">JavaScript ' + puzzleName + '</b></font>',
   '<table border="0" id="SETTINGS" width="100%">',
   '<tr><td valign="bottom" colspan="3">&nbsp;<br><b>Highlight Color</b></td></tr>',
   '<tr><td>&nbsp;&nbsp;</td><td colspan="2">',
   '<table border="1" id="COLOR" width="300">',
   '<tr> <td id="CSET" rowspan="6" width="33%" bgcolor="FFD0D0"></td>',
   '     <td onclick="setShowColor(' + "'" + '#FFD0D0' + "'" + ')" bgcolor="#FFD0D0" width="67%">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'" + '#D0FFD0' + "'" + ')" bgcolor="#D0FFD0">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'" + '#D0D0FF' + "'" + ')" bgcolor="#D0D0FF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'" + '#D0FFFF' + "'" + ')" bgcolor="#D0FFFF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'" + '#FFD0FF' + "'" + ')" bgcolor="#FFD0FF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'" + '#FFFFD0' + "'" + ')" bgcolor="#FFFFD0">&nbsp;</td> </tr>',
   '</table>',
   '</td></tr>',
   '<tr><td valign="bottom" colspan="3">&nbsp;<br><b>Actions</b></td></tr>',
   '<tr><td>&nbsp;&nbsp;</td><td colspan="2" onclick="clearBoard()">  Clear the board       </td></tr>',
   '<tr onclick="resetBorders()">',
   '   <td valign="bottom" colspan="3">&nbsp;<br><b>Table Borders</b></td></tr>',
   '<tr onclick="toggleBorder(0)"><td>&nbsp;</td>',
   '   <td align="center" width="10%"><font size="4" id="border1">&nbsp;</font></td>',
   '   <td id="border1title">&nbsp;</td></tr>',
   '<tr onclick="toggleBorder(1)"><td>&nbsp;</td>',
   '   <td align="center"><font size="4" id="border2">&nbsp;</font></td>',
   '   <td id="border2title">&nbsp;</td></tr>',
   '<tr onclick="toggleBorder(2)"><td>&nbsp;</td>',
   '   <td align="center"><font size="4" id="border3">&nbsp;</font></td>',
   '   <td id="border3title">&nbsp;</td></tr>',
   '<tr onclick="toggleBorder(3)"><td>&nbsp;</td>',
   '   <td align="center"><font size="4" id="border4">&nbsp;</font></td>',
   '   <td id="border4title">&nbsp;</td></tr>',
   '<tr onclick="toggleBorder(4)"><td>&nbsp;</td>',
   '   <td align="center"><font size="4" id="border5">&nbsp;</font></td>',
   '   <td id="border5title">&nbsp;</td></tr>',
   '<tr onclick="toggleBorder(5)"><td>&nbsp;</td>',
   '   <td align="center"><font size="4" id="border6">&nbsp;</font></td>',
   '   <td id="border6title">&nbsp;</td></tr>',
   '</table>',
   '',
   '</td>',
   '</tr>',
   '<tr>',
   '<td colspan="5">',
   '',
   '<table border="0" width="100%" id="MESSAGES">',
   '<tr> <td> <font size="4"> <b id="MESSAGE1"> &nbsp; </b> </font> </td> </tr>',
   '<tr> <td> <font size="4" '+ 'id="MESSAGE2"> &nbsp;      </font> </td> </tr>',
   '</table>',
   '',
   '</td>',
   '</tr>',
   '</table>',
   '<h2 id="BANNERH2">&nbsp;</h2>',
   '<script>',
   'scanTable("GAMEID")',
   '</' + 'script>',
   '</body>',
   '</html>')

/*
** Launch a Generic Puzzle
*/
function playGeneric(x)
{
   var any
   var str
   var page

   gameWindowOptions = "top=0," +
                       "height=" + gameWinHigh + "," +
                       "width="  + gameWinWide + "," +
                       "left="   + playOffset  + "," +
                       "toolbar=yes," +
                       "location=no," +
                       "resizable=yes," +
                       "scrollbars=yes"
   gameWindow = window.open("","Puzzle_"+x,gameWindowOptions)

   any = gameWindow.document.getElementsByTagName("table")
   if ( any.length )
     {
      /*
      ** This window already has tables.
      ** It must be left over from some previous instantiation
      ** of game playing.  Since we don't know what this window
      ** contains, close it and reopen it.
      */
      if (debugMode)
        { alert("This game window already has "+any.length+" tables!") }
      gameWindow.close()
      gameWindow = window.open("","Puzzle_"+x,gameWindowOptions)
     }

   page = ''
   for ( n=0; n<gameHtml.length; n++ )
   {
      str = gameHtml[n] + "\n"
      str = str.replace("GAMEID",x)
      page += str
   }

   if (showGameHtml)
   {
      page = page.replace(/&/g,"&amp;")
      page = page.replace(/</g,"&lt;")
      page = page.replace(/>/g,"&gt;")

      page =
         '<!DOCTYPE html>'                                      + "\n" +
         '<html lang="en">'                                     + "\n" +
         '<head>'                                               + "\n" +
         '<meta charset="utf-8"/>'                              + "\n" +
         '<title>' + puzzleName + puzzleType + ' HTML</title>'  + "\n" +
         '</head>'                                              + "\n" +
         '<body>'                                               + "\n" +
         '<h1>'    + puzzleName + puzzleType + ' HTML</h1>'     + "\n" +
         '<pre>'                                                + "\n" +
         page                                                          +
         '</pre>'                                               + "\n" +
         '</body>'                                              + "\n" +
         '</html>'                                              + "\n"
   }

   gameWindow.document.write(page)
}


//***********************
//***********************
//**                   **
//**  Debug Functions  **
//**                   **
//***********************
//***********************
debugMode      = 0
debugWindow    = 0
debugNotScript = 0

function debugDOM(v)
{
   var tlist
   var n
   var table
   var ident

// alert("Entering debugDOM("+v+")")

   if ( (0 == debugWindow) || (debugWindow.closed) )
     {
      /*
       * Open a new debug window.
       */
       debugWindow =
          window.open("","debug",
             "toolbar=yes,"+
             "scrollbars=yes,"+
             "location=yes,"+
             "resizable=yes")

       debugWindow.document.write(
          '<html>'
        + '<head>'
        + '<title>' + puzzleName + puzzleType + ' ' + Version + ' DOM Log</title>'
        + '</head>'
        + '<body>'
        + '<h1>'    + puzzleName + puzzleType + ' ' + Version + ' DOM Log</h1>'
      )
     }
   else
     {
      /*
       * Debug window is already open.
       * Mark a new section with a horizontal line.
       */
      debugWindow.document.write("<hr>")
     }

   /* Parse the TABLE elements and build the arrays of table data */
   tlist = document.getElementsByTagName("TABLE")
   debugWindow.document.write('<h3>Entered debugDOM('+v+')</h3>')

   if (-1 == v)
   {
      debugWindow.document.write('<p>Number of tables is '+tlist.length+'.<ol>')
      n = 0
      while(tlist[n])
        {
         table = tlist[n]
         ident = table.getAttribute("id")
         if (ident == null)
           { ident = "" }
         debugWindow.document.write('<li>'+table.nodeName+'('+table.nodeType+') id=('+ident+')')
         dumpItem(table)
         debugWindow.document.write('</li>')
         n++
        }
      debugWindow.document.write('</ol>End of tables.')
   }
   else if (-2 == v)
   {
      debugWindow.document.write('<p>Document Body.<br>')
      dumpTree(document.body)
      debugWindow.document.write('<br>End of Document Body.')
   }
   else if (-3 == v)
   {
      debugWindow.document.write('<p>Something completely different.<br>')
   }
   else if (-4 == v)
   {
      debugWindow.document.write('<p>Something entirely different.<br>')
   }
   else if (0 < v)
   {
      debugWindow.document.write('<p>Table '+v+'.<ol>')
      if (v <= tlist.length)
      {
         table = tlist[v-1]
         ident = table.getAttribute("id")
         if (ident == null)
           { ident = "" }
         debugWindow.document.write('<li>'+table.nodeName+'('+table.nodeType+') id=('+ident+')')
         dumpItem(table)
         debugWindow.document.write('</li>')
      }
      else
      {
         debugWindow.document.write('<li>Oops.  There is no Table '+v+'.</li>')
      }
      debugWindow.document.write('</ol>End of Table '+v+'.')
   }

   debugWindow.document.write('<h3>Exiting debugDOM('+v+')</h3>')

// alert("Exiting debugDOM()")
}

function debug(msg)
{
   if (debugMode)
   {
      if (debugWindow)
          debugWindow.document.write(msg+"<br>")
      else
          alert(msg)
   }
}

function debugSpacer(n)
{
   if ( (keyFlags & KEYFLAG_CTRL) &&
        (keyFlags & KEYFLAG_ALT) )
   {
      debugDOM(n)
   }
}

function dumpTree(item)
{
   var ident

   debug("Entering dumpTree()")

   ident = item.getAttribute("id")
   if (ident == null)
     { ident = "" }
   debugWindow.document.write('<ol><li>'+item.nodeName+'('+item.nodeType+') id=('+ident+')')
   dumpItem(item)
   debugWindow.document.write('</li></ol>')

   debug("Exiting dumpTree()")
}

function dumpItem(item)
{
   var i
   var list = item.childNodes
   var node
   var ident

   if (item.nodeName=='A')
     {
      hrefstr = item.getAttribute('href')
      debugWindow.document.write('(href="'+hrefstr+'"')
      ttipstr = item.getAttribute('title')
      debugWindow.document.write(' title="'+ttipstr+'"')
      debugWindow.document.write(')')
     }

   debugWindow.document.write(' has '+list.length+' child(ren).')
   dumpAttrs(item)
   debugWindow.document.write('<ol>')
   for( i=0; i<list.length; i++ )
     {
      node = list[i]
      debugWindow.document.write('<li>'+node.nodeName+'('+node.nodeType+')')

      if (node.nodeType==1)
        {
         ident = node.getAttribute("id")
         if (ident == null)
           { ident = "" }
         debugWindow.document.write(' id=('+ident+')')
         if (node.nodeName=="TABLE")
           {
            debugWindow.document.write(' has '+node.childNodes.length+' child(ren).')
            dumpAttrs(node)
           }
         else
           {
            if (node.nodeName=='SCRIPT')
               debugNotScript = 1
            dumpItem(node)
            if (node.nodeName=='SCRIPT')
               debugNotScript = 0
           }
        }
      else if ( (node.nodeType==3) || (node.nodeType==8) )
        {
         /* Build the debug string telling where we are */
         dbgmsg=''

         /* Ignore blank strings */
         node_data = node.data.replace(/\n/g,"")
         node_data = node_data.replace(/^ /g,"")
         node_data = node_data.replace(/ $/g,"")
         if ( node_data == "" )
           {
            dbgmsg=dbgmsg+' [blanks]'
           }

         /*
          * For all text nodes, tell us where we are.
          */
         node_data = node.data
         if (debugNotScript)
            node_data = '(suppressed)'
         debugWindow.document.write(
            '="'+node_data+'"'+
            ' ('+node.data.length+')'+
                 dbgmsg+'.')
        }
      else
        {
         debugWindow.document.write(' which is an unexpected node type.')
         dumpAttrs(node)
        }
      debugWindow.document.write('</li>')
     }
   debugWindow.document.write('</ol>')
}

function dumpAttrs(item)
{
   var map
   var i
   var node

   map = item.attributes
   if (map.length > 0)
   {
      for( i=0; i<map.length; i++ )
      {
         node = map[i]
         debugWindow.document.write('<br>'+nbsp+nbsp+nbsp
                                  + 'attr('+i+') is '+node.nodeName
                                                 +'('+node.nodeType+')="'
                                                 +    node.nodeValue+'"')
      }
   }
   else
   {
      debugWindow.document.write('<br>no attrs')
   }
}


//***************************
//***************************
//**                       **
//** Key Stroke Management **
//**                       **
//***************************
//***************************
/*
**   * OnKeyDown  turns on various flags depending on the key pressed.
**   * OnKeyUp    turns off those flags.
**   * When mouse is clicked (OnClick or OnDblClick), checking those
**     flags and changing behavior lets keys act like Apple/Option keys.
*/
document.onkeydown = genericKeyDn
document.onkeyup   = genericKeyUp
if (document.layers)
{
   document.captureEvents(Event.KEYDOWN)
   document.captureEvents(Event.KEYUP)
}

KEY_SHIFT      = 16
KEY_CTRL       = 17
KEY_ALT        = 18

keyFlags       = 0
KEYFLAG_SHIFT  = 1
KEYFLAG_CTRL   = 2
KEYFLAG_ALT    = 4

flashAgain     = 0

function genericKeyDn(evt)
{
   if (evt)
     { theKey = evt.which }
   else
     { theKey = window.event.keyCode }
   if (theKey == KEY_SHIFT) { keyFlags |= KEYFLAG_SHIFT }
   if (theKey == KEY_CTRL ) { keyFlags |= KEYFLAG_CTRL  }
   if (theKey == KEY_ALT  ) { keyFlags |= KEYFLAG_ALT   }

   if ( (keyFlags & KEYFLAG_CTRL) &&
        (keyFlags & KEYFLAG_ALT) )
     {
      /* Different ways to find the digit 0 on the keyboard */
      if (theKey == 45 || theKey == 48 || theKey == 96)
        {
/***     flashFinal()     ***/
         flashAgain = 1
        }
     }
//
// The decimal digits are determined with 3 keycodes which are:
//    Numlock off number pad codes
//      36[0x24]=home         38[0x26]=up arrow     33[0x21]=page up
//      37[0x25]=left arrow   12[0x0C]=?            39[0x27]=right arrow
//      35[0x23]=end          40[0x28]=down arrow   34[0x22]=page down
//      45[0x2D]=insert                             46[0x2E]=delete
//    ASCII digit codes  (48[0x30]=0  through   57[0x39]=9)
//    Number pad codes   (96[0x60]=0  through  105[0x69]=9)
// if (theKey == 45 || theKey == 48 || theKey ==  96) { selectNumber(0) }
// if (theKey == 35 || theKey == 49 || theKey ==  97) { selectNumber(1) }
// if (theKey == 40 || theKey == 50 || theKey ==  98) { selectNumber(2) }
// if (theKey == 34 || theKey == 51 || theKey ==  99) { selectNumber(3) }
// if (theKey == 37 || theKey == 52 || theKey == 100) { selectNumber(4) }
// if (theKey == 12 || theKey == 53 || theKey == 101) { selectNumber(5) }
// if (theKey == 39 || theKey == 54 || theKey == 102) { selectNumber(6) }
// if (theKey == 36 || theKey == 55 || theKey == 103) { selectNumber(7) }
// if (theKey == 38 || theKey == 56 || theKey == 104) { selectNumber(8) }
// if (theKey == 33 || theKey == 57 || theKey == 105) { selectNumber(9) }
//
//    The following are just the ASCII codes for the upper case letters
// if (theKey == 65) { flashLetter('A'); flashAgain = 1 }
// if (theKey == 66) { flashLetter('B'); flashAgain = 1 }
// if (theKey == 67) { flashLetter('C'); flashAgain = 1 }
// if (theKey == 68) { flashLetter('D'); flashAgain = 1 }
// if (theKey == 69) { flashLetter('E'); flashAgain = 1 }
// if (theKey == 70) { flashLetter('F'); flashAgain = 1 }
// if (theKey == 71) { flashLetter('G'); flashAgain = 1 }
// if (theKey == 72) { flashLetter('H'); flashAgain = 1 }
// if (theKey == 73) { flashLetter('I'); flashAgain = 1 }
// if (theKey == 74) { flashLetter('J'); flashAgain = 1 }
// if (theKey == 75) { flashLetter('K'); flashAgain = 1 }
// if (theKey == 76) { flashLetter('L'); flashAgain = 1 }
// if (theKey == 77) { flashLetter('M'); flashAgain = 1 }
// if (theKey == 78) { flashLetter('N'); flashAgain = 1 }
// if (theKey == 79) { flashLetter('O'); flashAgain = 1 }
// if (theKey == 80) { flashLetter('P'); flashAgain = 1 }
// if (theKey == 81) { flashLetter('Q'); flashAgain = 1 }
// if (theKey == 82) { flashLetter('R'); flashAgain = 1 }
// if (theKey == 83) { flashLetter('S'); flashAgain = 1 }
// if (theKey == 84) { flashLetter('T'); flashAgain = 1 }
// if (theKey == 85) { flashLetter('U'); flashAgain = 1 }
// if (theKey == 86) { flashLetter('V'); flashAgain = 1 }
// if (theKey == 87) { flashLetter('W'); flashAgain = 1 }
// if (theKey == 88) { flashLetter('X'); flashAgain = 1 }
// if (theKey == 89) { flashLetter('Y'); flashAgain = 1 }
// if (theKey == 90) { flashLetter('Z'); flashAgain = 1 }
}

function genericKeyUp(evt)
{
   if (evt)
     { theKey = evt.which }
   else
     { theKey = window.event.keyCode }
   if (theKey == KEY_SHIFT) { keyFlags &= ~KEYFLAG_SHIFT }
   if (theKey == KEY_CTRL ) { keyFlags &= ~KEYFLAG_CTRL  }
   if (theKey == KEY_ALT  ) { keyFlags &= ~KEYFLAG_ALT   }
   if (flashAgain)
     {
/***  flashBoard()  ***/
      flashAgain = 0
     }
}


//*************************
//*************************
//**                     **
//**  Generic Functions  **
//**                     **
//*************************
//*************************
colorSet       = 0
bannerH1       = 0
bannerH3       = 0
menuTitle      = 0
msg1node       = 0
msg2node       = 0
msg1text       = 0
msg2text       = 0

borderTable    = 0
borderDefault  = 0
borderValue    = 0
borderCheck    = 0
borderTitle    = 0

tableID        = new Array
tableDesc      = new Array
tables         = 0


function genericTableDesc()
{
   debug("Entering genericTableDesc()")

   /* Populate the table description table */
   lcType = puzzleType.toLowerCase()
   tableID[tables] = "PAGE";       tableDesc[tables++] = "Enclosing table for the web page"
   tableID[tables] = "BOARD";      tableDesc[tables++] = puzzleName + lcType + " board"
   tableID[tables] = "SETTINGS";   tableDesc[tables++] = "Settings table"
   tableID[tables] = "COLOR";      tableDesc[tables++] = "Color selector table"
   tableID[tables] = "COLOR1";     tableDesc[tables++] = "Color selector table"
   tableID[tables] = "COLOR2";     tableDesc[tables++] = "Color assignment table"
   tableID[tables] = "MESSAGES";   tableDesc[tables++] = "Messages table"

   debug("Exiting genericTableDesc()")
}

function genericScan(fileName)
{
   var  lcType
   var  puzzleBanner
   var  gameBanner
   var  aboutTitle
   var  yy
   var  mm
   var  dd
   var  i

   debug("Entering genericScan("+fileName+")")

   /* Populate the table description table */
   if (tableID.length <= 0)
      genericTableDesc()

   /* Compute the puzzle pieces */
   document.title = "JavaScript " + puzzleName + " Version " + Version
   yy = fileName.substr(2,2)
   mm = fileName.substr(4,2)
   dd = fileName.substr(6,2)
   puzzleBanner = puzzleName + puzzleType
     gameBanner = "Austin American-Statesman " + mm + "/" + dd + "/20" + yy
    aboutTitle  = document.title + "\n"
   if ("Generic" != puzzleName)
      aboutTitle += "JavaScript Generic Version " + GenVersion + "\n"
   aboutTitle    += "Copyright (c) "   + Copyright

   /* Find the colorSet, banner, and message text #TEXT nodes */
   colorSet  = document.getElementById("CSET")
   bannerH1  = document.getElementById("BANNERH1")
   bannerH3  = document.getElementById("BANNERH3")
   menuTitle = document.getElementById("menuTitle")
   msg1node  = document.getElementById("MESSAGE1")
   msg2node  = document.getElementById("MESSAGE2")
   msg1text  = msg1node.childNodes[0]
   msg2text  = msg2node.childNodes[0]

   /* Fill in the values for several of those pieces */
   bannerH1.textContent = puzzleBanner
   bannerH3.textContent = gameBanner
   menuTitle.textContent = document.title
   menuTitle.setAttribute('title',aboutTitle)
   msg1text.data = nbsp
   msg2text.data = nbsp

   /* Get the initialized selection color */
   showColor = colorSet.getAttribute('bgcolor')
   
   /* Set up the table border toggles */
   populateBorderToggles()

   debug("Exiting genericScan()")
}

function bail(err)
{
   alert(err)
   window.stop
}

function setShowColor(x)
{
   debug("Entering setShowColor('"+x+"')")

   showColor = x
   colorSet.setAttribute('bgcolor',showColor)

   debug("Exiting setShowColor()")
}

function populateBorderToggles()
{
   var  tlist
   var  tabid
   var  n
   var  i
   var  x

   debug("Entering populateBorderToggles()")

   /* Populate the table border settings */
   borderTable   = new Array
   borderDefault = new Array
   borderValue   = new Array
   borderCheck   = new Array
   borderTitle   = new Array
   tlist = document.getElementsByTagName("TABLE")
   for( n=0; n<tlist.length; n++ )
   {
      x = n + 1
      borderTable[n] = tlist[n]
      borderCheck[n] = document.getElementById("border"+x).childNodes[0]
      borderTitle[n] = document.getElementById("border"+x+"title").childNodes[0]
      if (borderTable[n].getAttribute("border") == 0)
      {
         borderDefault[n] = borderValue[n] = 0
         borderCheck[n].data = char2610
      }
      else
      {
         borderDefault[n] = borderValue[n] = 1
         borderCheck[n].data = char2611
      }
      tabid = borderTable[n].getAttribute('ID')
      borderTitle[n].data = x + ": " + tabid
      for( i=0; i<tables; i++ )
      {
         if ( tableID[i] == tabid )
         {
            borderTitle[n].data = x + ": " + tableDesc[i]
         }
      }
   }

   debug("Exiting populateBorderToggles()")
}

function toggleBorder(n)
{
   debug("Entering toggleBorder("+n+")")

   if (borderValue[n])
     {
      borderCheck[n].data = char2610
      borderTable[n].setAttribute('border',0)
      borderValue[n] = 0
     }
   else
     {
      borderCheck[n].data = char2611
      borderTable[n].setAttribute('border',1)
      borderValue[n] = 1
     }

   debug("Exiting toggleBorder()")
}

function resetBorders()
{
   var  n

   debug("Entering resetBorders()")

   for( n=0; n<borderTable.length; n++ )
     {
      if (borderDefault[n] == 0)
        {
         borderCheck[n].data = char2610
         borderTable[n].setAttribute('border',0)
         borderValue[n] = 0
        }
      else
        {
         borderCheck[n].data = char2611
         borderTable[n].setAttribute('border',1)
         borderValue[n] = 1
        }
     }

   debug("Exiting resetBorders()")
}


//************************
//************************
//**                    **
//**  Puzzle Functions  **
//**                    **
//************************
//************************
showColor      = 0
clearColor     = "WHITE"
boardCELL      = 0
boardFONT      = 0
boardTEXT      = 0
targets        = 0
initial        = 0
solution       = 0
hardcode       = new Array ( '11', '12', '21', '22' )

function scanTable(fileName)
{
   var  i

   debug("Entering scanTable("+fileName+")")

   /* Get the Generic stuff set up */
   genericScan(fileName)

   /* Stuff specific to this sample puzzle */
   eval( "targets = targets_"+fileName)
   eval( "initial = initial_"+fileName)
   eval("solution =   final_"+fileName)

   /* Find the puzzle matrix */
   boardCELL = document.getElementsByName("BOARDCELL")
   boardFONT = new Array
   boardTEXT = new Array
   for( i=0; i<boardCELL.length; i++ )
     {
      boardFONT[i] = boardCELL[i].childNodes[0]
      boardTEXT[i] = boardFONT[i].childNodes[0]
     }

   /* Get our color starting point */
   showColor = colorSet.getAttribute('bgcolor')

   /* Initial messages */
   msg1text.data = "To begin:"
   msg2text.data = "Click on any cell of the puzzle matrix."

   debug("Exiting scanTable()")
}

function clearBoard()
{
   var i

   debug("Entering clearBoard()")

   msg1text.data = nbsp
   msg2text.data = nbsp

   for( i=0; i<boardCELL.length; i++ )
     {
      boardCELL[i].setAttribute('bgcolor',clearColor)
      boardTEXT[i].data = nbsp
     }

   debug("Exiting clearBoard()")
}

function clickCell(n)
{
   var i

   debug("Entering clickCell("+n+")")

   /* Clear messages */
   msg1text.data = nbsp
   msg2text.data = nbsp

   /* Change the color of the clicked cell */
   boardCELL[n].setAttribute('bgcolor',showColor)

   /* Change the contents of the clicked cell, if indicated */
   if ( (keyFlags & KEYFLAG_CTRL) &&
        (keyFlags & KEYFLAG_ALT) )
     {
        boardTEXT[n].data = targets[n]
     }
   else if (keyFlags & KEYFLAG_CTRL)
     {
        boardTEXT[n].data = initial[n]
     }
   else if (keyFlags & KEYFLAG_ALT)
     {
        boardTEXT[n].data = solution[n]
     }
   else if (keyFlags & KEYFLAG_SHIFT)
     {
        boardTEXT[n].data = hardcode[n]
     }
   else
     {
     // Do nothing
     }

   debug("Exiting clickCell()")
}
