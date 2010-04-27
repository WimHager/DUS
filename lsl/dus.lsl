//    This file is part of DUS.
//
//    DUS is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    DUS is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with DUS.  If not, see <http://www.gnu.org/licenses/>.

//    DUS is Dynamic UUID Server for Secondlife
//    W. Hager founder and project leader

string  Url= "http://serverurl/dus.php";	  //url of DUS server
integer TTL= 43200;                               //Time to live cycle
integer Channel= 999;                             //linked message channel for communication with other scripts

//Example DUS call
//http://DUS_SERVER/dus.php?uuid=74d79e9d-2cfd-e432-aabc-0688c5f4757c
//where uuid is Prim Key

//////////////////////////
key     HttpRequestId;
list    HttpData;
key     RequestURL;
string  PrimUrl; 
integer Debug= FALSE;
  

list ParsePostData(string Message) {
    list PostData= []; // The list with the data that was passed in.
    list ParsedMessage= llParseString2List(Message,["&"],[]); // The key/value pairs parsed into one list.
    integer Len=~llGetListLength(ParsedMessage);
 
    while(++Len) {          
        string CurrentField= llList2String(ParsedMessage, Len); // Current key/value pair as a string.
 
        integer Split= llSubStringIndex(CurrentField,"="); // Find the "=" sign
        if(Split == -1) { // There is only one field in this part of the message.
            PostData+= [llUnescapeURL(CurrentField),""];  
        } else {
            PostData+= [llUnescapeURL(llDeleteSubString(CurrentField,Split,-1)), llUnescapeURL(llDeleteSubString(CurrentField,0,Split))];
        }
    }
    // Return the strided list.
    return PostData;
}


DusUpd() { //Upates DUS server
        string ParsStr= "url="+PrimUrl+"&ttl="+(string)TTL+"&func=UPD";
        HttpRequestId= llHTTPRequest(Url,[HTTP_METHOD, "POST",HTTP_MIMETYPE,"application/x-www-form-urlencoded"],ParsStr); 
        if (Debug) llOwnerSay("DUS update");
}       
    
default
{
    state_entry() {
        RequestURL= llRequestURL(); //Request for free url
        llSetTimerEvent(1);
    }
    
    timer() {
        DusUpd(); //Update DUS server
        llSetTimerEvent(TTL/2); //Refersh at twice as fast to be sure
    }
    
    touch_start(integer Touches) {
        RequestURL= llRequestURL(); //Refreh on touch
        llSetTimerEvent(1);
    }        
        
    http_response(key RequestId, integer Status, list MetaData, string Body) {
        if (Status == 200) {
            llSetText("",<0,0,0>,0);
            //if (RequestId == HttpRequestId)  //llOwnerSay(Body+"\n");
        }else{ llSetText("Server error: "+(string)Status,<1,1,1>,1); }
    }

    http_request(key Id, string Method, string Body) {
        list IncomingMessage;
 
        if ((Method == URL_REQUEST_GRANTED) && (Id == RequestURL) ){
            // An URL has been assigned to me.
            if (Debug) llOwnerSay("Obtained URL: " + Body);
            llSetText(Body,<1,1,1>,1);
            PrimUrl= Body;            
            RequestURL = NULL_KEY;
        }
        else if ((Method == URL_REQUEST_DENIED) && (Id == RequestURL)) {
            // I could not obtain a URL
            if (Debug) llOwnerSay("There was a problem, and an URL was not assigned: " + Body);
            RequestURL = NULL_KEY;
        }
        else if (Method == "POST") { 
            // An incoming message was received.
            if (Debug) llOwnerSay("Received information form the outside.");
            IncomingMessage= ParsePostData(Body);
            if (Debug) llOwnerSay(llDumpList2String(IncomingMessage,"\n"));
            llMessageLinked(LINK_THIS, Channel, llDumpList2String(IncomingMessage,"\n"), NULL_KEY);
            llHTTPResponse(Id,200,"You passed the following:\n" + 
                           llDumpList2String(IncomingMessage,"\n"));
 
        }
        else {
            // An incoming message has come in using a method that has
            // not been anticipated.
            llHTTPResponse(Id,405,"Unsupported Method");
        }
    }
    
    changed(integer Change)  { //Update forced at change
        if (Change & (CHANGED_REGION | CHANGED_REGION_START | CHANGED_TELEPORT) ) {
            llResetScript();
        }
    }
        
    on_rez(integer StartPrm) {
        llResetScript(); //Reset on rez 
    }
    
}
