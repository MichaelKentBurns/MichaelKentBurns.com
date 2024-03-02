/******************************************/
/* Freds Lunch             in Javascript **/
/* Copyright (c) 2022        Greg Grimes **/
/******************************************/
/*
**
** This utility mimics the Excel spreadsheet created for the
** Freds Lunch record keeping.
**
** Bugs:
**
** To Do:
**    * Add an option to not clear the table each time
**X   * Create the other years' JS definitions
**
** Versions:
**    4.01 - Initial coding 
**    4.02 - Accommodate varying numbers of Freds
**    4.03 - Add the Reverse the table action
**    4.04 - Add extra RowBalance option
*/
Version            = "4.04"
//owMenuHtml       = 1
//owGameHtml       = 1
puzzleTitle        = "Freds Lunch"
puzzleName         = "FredsLunch"
puzzleType         = ""
playOffset         = 100
gameWinHigh        = 900
gameWinWide        = 1400
boardOrder         = 0
boardTable         = 0
boardTbody         = 0
fredCELL           = 0
initRow            = 0
initRowB           = 0
initCELL           = 0
topRow             = 0
topRowB            = 0
topCELL            = 0
bottomRow          = 0
bottomRowB         = 0
bottomCELL         = 0
insertRow          = 0
spaceRow           = 0
lunchRow           = 0
lunchRows          = 0
gamename           = 0
showYear           = 0
fromYear           = 0
freds              = 0
initial            = 0
lunches            = 0
optionXtraRowB     = 1
charXtraRowB       = 0

/*
** Freds 4.04.htm
** as a JavaScript array
*/
gameHtml = new Array (
   '<!DOCTYPE html>',
   '<html lang="en">',
   '<head>',
   '<meta charset="utf-8"/>',
   '<title>Freds</title>',
   '<script language="JavaScript" type="text/javascript" src="generic.js"></script>',
   '<script language="JavaScript" type="text/javascript" src="Freds ' + Version + '.js"></script>',
   '<script language="JavaScript" type="text/javascript" src="GAMEID.js"></script>',
   '</head>',
   '<body>',
   '<h1 id="BANNERH1">&nbsp;</h1>',
   '<h3 id="BANNERH3">&nbsp;</h3>',
   '<table border="0" id="PAGE">',
   '<tr>',
   '<td>',
   '',
   '<font size="5"><b id="GAMENAME">&nbsp;</b></font>',
   '<table border="1" cellpadding="5" id="BOARD">',
   '<tr>',
   '   <th align="center" width="50">Date</th>',
   '   <th align="center" width="50">DateText</th>',
   '   <th align="center" width="10">&nbsp;</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(1)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(2)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(3)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(4)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(5)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(6)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(7)">Fred</th>',
   '   <th align="center" width="20" name="FRED" onclick="debugDOM(8)">Fred</th>',
   '   <th align="center" width="10">&nbsp;</th>',
   '   <th align="center" width="50">RowBalance</th>',
   '   <th align="center" width="10">&nbsp;</th>',
   '   <th align="center" width="200">Where</th>',
   '</tr>',
   '<tr id="TOPROW">',
   '   <td align="center">(numeric)</td>',
   '   <td align="center">(text)</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center" name="TOPCELL">X</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" id="topRowB">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '<tr>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '<tr id="INITROW">',
   '   <td align="center" id="FROMYEAR">From before</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center" name="INITCELL">X</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" id="initRowB">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '<tr id="SPACEROW">',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '<tr id="INSERTROW">',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '<tr id="BOTTOMROW">',
   '   <td align="center">ColumnBalance</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center" name="BOTTOMCELL">X</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center" id="bottomRowB">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '   <td align="center">&nbsp;</td>',
   '</tr>',
   '</table>',
   '',
   '</td>',
   '<td width="25">&nbsp;</td>',
   '<td valign="top">',
   '',
   '<font size="4"><b id="menuTitle">JavaScript Freds Lunch</b></font>',
   '<table border="0" id="SETTINGS" width="100%">',
   '<tr><td valign="bottom" colspan="3">&nbsp;<br><b>Highlight Color</b></td></tr>',
   '<tr><td>&nbsp;&nbsp;</td><td colspan="2">',
   '<table border="1" id="COLOR" width="300">',
   '<tr> <td id="CSET" rowspan="6" width="33%" bgcolor="FFD0D0"></td>',
   '     <td onclick="setShowColor(' + "'#FFD0D0'" + ')" bgcolor="#FFD0D0" width="67%">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'#D0FFD0'" + ')" bgcolor="#D0FFD0">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'#D0D0FF'" + ')" bgcolor="#D0D0FF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'#D0FFFF'" + ')" bgcolor="#D0FFFF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'#FFD0FF'" + ')" bgcolor="#FFD0FF">&nbsp;</td> </tr>',
   '<tr> <td onclick="setShowColor(' + "'#FFFFD0'" + ')" bgcolor="#FFFFD0">&nbsp;</td> </tr>',
   '</table>',
   '</td></tr>',
   '<tr><td valign="bottom" colspan="3">&nbsp;<br><b>Options</b></td></tr>',
   '<tr onclick="toggleXtraRowB()"><td>&nbsp;</td>',
   '   <td align="center"><font id="optionXtraRowB">&nbsp;</font></td>',
   '   <td>Row balance top and bottom</td></tr>',
   '<tr><td valign="bottom" colspan="3">&nbsp;<br><b>Actions</b></td></tr>',
   '<tr><td>&nbsp;&nbsp;</td><td colspan="2" onclick="resetBoard()">Reset the board</td></tr>',
   '<tr><td>&nbsp;&nbsp;</td><td colspan="2" onclick="reverseBoard()">Reverse the table order</td></tr>',
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
   '<tr> <td> <font size="4"     id="MESSAGE2"> &nbsp;      </font> </td> </tr>',
   '</table>',
   '',
   '</td>',
   '</tr>',
   '</table>',
   '<script>',
   'boardSetup("GAMEID")',
   '</script>',
   '</body>',
   '</html>',
   )

function menuItemFredsLunch(fileName)
{
   var  value

   debug("Entering menuItemFredsLunch("+fileName+")")

   /* File names should be like "fred2022" */
   if ("fred" == fileName.substr(0,4))
   {
      value = "For year " + fileName.substr(4,4)
   }
   else
   {
      value = fileName
   }

   debug("Exiting menuItemFredsLunch() with '"+value+"'")
   return value
}

function playFredsLunch(fileName)
{
   debug("Entering playFredsLunch()")

   /* Suprisingly nothing special for this application */
   playGeneric(fileName)

   debug("Exiting playFredsLunch()")
}

function boardSetup(fileName)
{
   var  gamename

   debug("Entering boardSetup("+fileName+")")

   /* Get the Generic stuff set up */
   genericScan(fileName)

   /* Find and populate the things peculiar to Freds Lunch */
     fredCELL = document.getElementsByName("FRED")
     initCELL = document.getElementsByName("INITCELL")
      topCELL = document.getElementsByName("TOPCELL")
   bottomCELL = document.getElementsByName("BOTTOMCELL")
     initRow  = document.getElementById("INITROW")
     initRowB = document.getElementById("initRowB")
      topRow  = document.getElementById("TOPROW")
      topRowB = document.getElementById("topRowB")
   bottomRow  = document.getElementById("BOTTOMROW")
   bottomRowB = document.getElementById("bottomRowB")
   insertRow  = document.getElementById("INSERTROW")
    spaceRow  = document.getElementById("SPACEROW")
     gamename = document.getElementById("GAMENAME")
     fromYear = document.getElementById("FROMYEAR")
     showYear = fileName.substr(4,4)
   document.title       = showYear + " " + puzzleTitle
   bannerH1.textContent =                  puzzleTitle
   bannerH3.textContent = "For "  +           showYear
   bannerH3.textContent = ""
   gamename.textContent = "For "  +           showYear
   fromYear.textContent = "From " + (parseInt(showYear) - 1)
   charXtraRowB = document.getElementById("optionXtraRowB").childNodes[0]
   if (optionXtraRowB)
      charXtraRowB.data = char2611
   else
      charXtraRowB.data = char2610

   /* Get this board's values */
   eval(  "freds =   freds_"+fileName)
   eval("initial = initial_"+fileName)
   eval("lunches = lunches_"+fileName)

   /* Populate the board */
   populateBoard()

   /* Complete getting the board to its starting point */
   resetBoard()

   debug("Exiting boardSetup()")
}

function populateBoard()
{
   var  list = new Array
   var  node
   var  i
   var  n
   var  x
   var  date
   var  mm
   var  dd
   var  sum
   var  row

   debug("Entering populateBoard()")

   //***************************
   //*  Validate incoming data *
   //***************************

   /* Remove all of the #text children from the BOARD table */
   boardTable = document.getElementById("BOARD")
   for( i=(boardTable.childNodes.length-1); i>=0; i-- )
   {
      node = boardTable.childNodes[i]
      if ( (node.nodeType==3) || (node.nodeType==8) )
          boardTable.removeChild( node )
   }
   boardTbody = boardTable.childNodes[0]
   if (boardTbody.nodeName != "TBODY")
      bail("Name of tbody is not TBODY but "+boardTbody.nodeName)

   /* Remove all of the #text children from the BOARD table's body */
   for( i=(boardTbody.childNodes.length-1); i>=0; i-- )
   {
      node = boardTbody.childNodes[i]
      if ( (node.nodeType==3) || (node.nodeType==8) )
          boardTbody.removeChild( node )
   }

   /* We need enough FRED cells for all specified Freds */
   if (freds.length > fredCELL.length)
      bail("FATAL: There aren't enough FRED cells ("
           +       fredCELL.length
           +    ") to accommodate all of the Freds ("
           +       freds.length
           +    ").")

   /* There should be the same number of initial values as Freds */
   if (fredCELL.length != initCELL.length)
      bail("FATAL: initCELL.length("
           +       initCELL.length
           +    ") not the same as"
           +     " fredCELL.length("
           +       fredCELL.length
           +    ").")

   /* Each lunch event should have a date, a place, and values for all Freds */
   n = lunches.length % (freds.length + 2)
   if (0 != n)
      bail("FATAL: The number of lunches("
           +       lunches.length
           +    ") is not an even multiple"
           +     " of the number of Freds ("
           +       freds.length
           +    ") plus date and place, but has ("
           +       n
           +    ") values left over.")

   /* Hide the extras if we have too many FREDs columns */
   if ( freds.length < fredCELL.length )
   {
      /* Hide the extras in each row */
      for( i=0; i<boardTbody.childNodes.length; i++ )
      {
         var  tr
         var  id
         var  td
         var  j

         tr = boardTbody.childNodes[i]
         id = tr.getAttribute('id')
         if ( i == 0 )
            removeExtras( fredCELL )
         else if ( id == "TOPROW" )
            removeExtras( topCELL )
         else if ( id == "INITROW" )
            removeExtras( initCELL )
         else if ( id == "BOTTOMROW" )
            removeExtras( bottomCELL )
         else
         {
            td = tr.childNodes
            j  = 0
            n  = fredCELL.length
            while (freds.length < n)
            {
               n--
               while ( 'TD' != td[j].nodeName )
                  j++
               td[j++].setAttribute('hidden','1')
            }
         }
      }
   }

   //****************************************
   //* Put the incoming data into the board *
   //****************************************

   /* Put each FRED's name in its column heading */
   for( i=0; i<fredCELL.length; i++ )
   {
      fredCELL[i].textContent = freds[i]
   }

   /* Put in the initial values carried over from last year */
   for( i=0; i<initCELL.length; i++ )
   {
      initCELL[i].textContent = initial[i]
   }

   /* Keep track of all the rows in the table */
   lunchRow  = new Array
   lunchRows = 0

   /* Insert an empty row for Jan 1 */
   list[0] = showYear + "/01/01"
   list[1] = "Jan 1"
   list[2] = nbsp
   n =  2
   for( x=0; x<freds.length; x++ )
   {
      n = x + 3
      list[n] = nbsp
   }
   list[n+1] = nbsp
   list[n+2] = "0"
   list[n+3] = nbsp
   list[n+4] = nbsp
   lunchRow[lunchRows++] = addBoardRow(list,"clickRow(0)")

   /* Insert rows in the table for each of the Freds Lunch events */
   row = 0
   for( i=0; i< (lunches.length/(freds.length+2)); i++ )
   {
      row++
      date    = lunches[((freds.length+2)*i)]
      mm      = date.substr(5,2)
      dd      = date.substr(8,2)
      list[0] = date
      list[1] = mNam[parseInt(mm)-1] + ' ' + parseInt(dd)
      list[2] = nbsp
      n =  2
      sum = 0
      for( x=0; x<freds.length; x++ )
      {
         n = x + 3
         if (""  !=   lunches[((freds.length+2)*i)+x+1])
         {
            list[n] = lunches[((freds.length+2)*i)+x+1]
            sum    +=
             parseInt(lunches[((freds.length+2)*i)+x+1])
         }
         else
            list[n] = nbsp
      }
      list[n+1] = nbsp
      list[n+2] = sum
      list[n+3] = nbsp
      list[n+4] = lunches[((freds.length+2)*(i+1))-1]
      lunchRow[lunchRows++] = addBoardRow(list,"clickRow("+row+")")
   }

   /* Insert an empty row for Dec 31 */
   list[0] = showYear + "/12/31"
   list[1] = "Dec 31"
   list[2] = nbsp
   n =  2
   for( x=0; x<freds.length; x++ )
   {
      n = x + 3
      list[n] = nbsp
   }
   list[n+1] = nbsp
   list[n+2] = "0"
   list[n+3] = nbsp
   list[n+4] = nbsp
   lunchRow[lunchRows++] = addBoardRow(list,"clickRow(1048576)")

   debug("Exiting populateBoard()")
}

function removeExtras(cells)
{
   var  n

   debug("Entering removeExtras("+cells+")")

   n = cells.length
   while (freds.length < n)
   {
      n--
      cells[n].setAttribute('hidden','1')
   }

   debug("Exiting removeExtras()")
}

function addBoardRow(values,link)
{
   var  tr
   var  td
   var  tx
   var  i

   debug("Entering addBoardRow()")

   tr = document.createElement("tr")
   boardTbody.insertBefore(tr,insertRow)
   for( i=0; i<values.length; i++ )
   {
      tx = document.createTextNode(values[i])
      td = document.createElement("td")
      td.setAttribute('align','center')
      td.appendChild(tx)
      tr.appendChild(td)
   }
   td.removeAttribute('align')
   if ("" != link)
      tr.setAttribute('onclick',link)

   debug("Exiting addBoardRow()")
   return tr
}

function computeTotals(toRow)
{
   var  x
   var  i
   var  sum
   var  fred

   debug("Entering computeTotals()")

   /* Compute totals for each person's column */
   for( x=0; x<freds.length; x++ )
   {
      sum = initial[x]
      if (toRow > 0)
      {
         for( i=0; i< (lunches.length/(freds.length+2)); i++ )
         {
            if (i >= toRow)
               break
            fred = lunches[((freds.length+2)*i)+x+1]
            if ("" != fred)
               sum += parseInt(fred)
         }
      }
      topCELL[x].textContent = sum
      if (toRow > 1000000)
         bottomCELL[x].textContent = sum
   }

   /* Include extra row balancing */
   extraRowBalancing()

   debug("Exiting computeTotals()")
}

function extraRowBalancing()
{
   var  i

   debug("Entering extraRowBalancing()")

   if (optionXtraRowB)
   {
      var sum
      var i

      sum = 0
      for( i=0; i<freds.length; i++ )
      {
         sum += parseInt(topCELL[i].textContent)
      }
      topRowB.textContent    = sum

      sum = 0
      for( i=0; i<freds.length; i++ )
      {
         sum += parseInt(initCELL[i].textContent)
      }
      initRowB.textContent   = sum

      sum = 0
      for( i=0; i<freds.length; i++ )
      {
         sum += parseInt(bottomCELL[i].textContent)
      }
      bottomRowB.textContent = sum
   }
   else
   {
      topRowB.textContent    = nbsp
      initRowB.textContent   = nbsp
      bottomRowB.textContent = nbsp
   }

   debug("Exiting extraRowBalancing()")
}

function clearBoard()
{
   var  i

   debug("Entering clearBoard()")

   /* Reset the top row to unselected color */
   topRow.setAttribute('bgcolor',clearColor)

   /* Reset all rows to unselected color */
   for( i=0; i<lunchRows; i++ )
   {
      lunchRow[i].setAttribute('bgcolor',clearColor)
   }

   debug("Exiting clearBoard()")
}

function resetBoard()
{
   debug("Entering resetBoard()")

   /* First clear the background colors on the board */
   clearBoard()

   /* Compute the totals for all rows */
   computeTotals(1048576)

   /* Start the game */
   msg1text.data = "NOTE:"
   msg2text.data = "Click on a row to see the column balances up to that date."

   debug("Exiting resetBoard()")
}

function reverseBoard()
{
   var  n

   debug("Entering reverseBoard()")

   /* First clear the background colors on the board */
   clearBoard()

   /* Remove all of the rows from the table */
   boardTbody.removeChild( initRow )
   boardTbody.removeChild( spaceRow )
   for( n=0; n<lunchRows; n++ )
      boardTbody.removeChild( lunchRow[n] )

   /* Switch from one order to the other */
   if (boardOrder)
   {
      boardTbody.insertBefore(  initRow,       insertRow )
      boardTbody.insertBefore( spaceRow,       insertRow )
      for( n=0; n<lunchRows; n++ )
         boardTbody.insertBefore( lunchRow[n], insertRow )
      boardOrder = 0
   }
   else
   {
      for( n=(lunchRows-1); n>=0; n-- )
         boardTbody.insertBefore( lunchRow[n], insertRow )
      boardTbody.insertBefore( spaceRow,       insertRow )
      boardTbody.insertBefore(  initRow,       insertRow )
      boardOrder = 1
   }

   /* Compute the totals for all rows */
   computeTotals(1048576)

   debug("Exiting reverseBoard()")
}

function clickRow(n)
{
   var  pct

   debug("Entering clickRow("+n+")")

   /* Clear the messages */
   msg1text.data = nbsp
   msg2text.data = nbsp

   /* First clear the background colors on the board */
   clearBoard()

   /* Now select the top row and the selected row */
   topRow.setAttribute('bgcolor',showColor)
   if (n > 1000000)
      lunchRow[lunchRows-1].setAttribute('bgcolor',showColor)
   else
      lunchRow[n].setAttribute('bgcolor',showColor)

   /* Compute the totals up to the specified row */
   computeTotals(n)

   debug("Exiting clickRow()")
}

function toggleXtraRowB()
{
   var  pct

   debug("Entering toggleXtraRowB()")

   /* Option for extra Row Balancing */
   if (optionXtraRowB)
   {
        charXtraRowB.data = char2610
      optionXtraRowB      = 0
   }
   else
   {
        charXtraRowB.data = char2611
      optionXtraRowB      = 1
   }

   /* Update those values */
   extraRowBalancing()

   debug("Exiting toggleXtraRowB()")
}
