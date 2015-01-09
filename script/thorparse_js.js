$(document).ready(function()
{
	RaidEncounterLogs    = $("#raidEncounterLogs tBody").children("tr");
	RaidEncounterActeurs = $("#raidEncounterActeurs").children("li");
	RaidEncounterFin   = Math.floor(parseInt(RaidEncounterLogs.last().children("td.logTime").html())/1000);
	RaidEncounterDebutm = parseInt(RaidEncounterLogs.first().children("td.logTime").html(),10);
	RaidEncounterDebut = Math.floor(RaidEncounterDebutm/1000);
	RaidEncounterMin = 0;
	RaidEncounterMax = Math.ceil(RaidEncounterFin-RaidEncounterDebut);
	RaidEncounterTimeLong= RaidEncounterFin - RaidEncounterDebut;
	
	
	ThorparseSandBox=$("#thorparseSandBox");
	thorparse_CalculeTout(false);
	
	if (!ThorparseSandBox.find("#loaded").length)
	{
		thorparse_afficher_tableau_de_bord();
		thorparse_afficher_summary();
		thorparse_afficher_charts();
		thorparse_afficher_tables();
		thorparse_table_sort();
		ThorparseSandBox.append("<div id=\"loaded\"></div>");
	}

	thorparse_listen_range();
});

function thorparse_CalculeTout(refresh)
{
	RaidEncounterActeursStats={};
	
	RaidEncounterDebutSelected=RaidEncounterDebut+RaidEncounterMin;
	RaidEncounterFinSelected  =RaidEncounterDebut+RaidEncounterMax;
	mRaidEncounterDebutSelected=RaidEncounterDebutSelected*1000;
	
	var RaidEncounterLogsSelected = RaidEncounterLogs.children("td.logTime").filter(function (index,value)
	{
		return Math.floor(parseInt($(value).text())/1000)>=RaidEncounterDebutSelected 
		&& 	   Math.floor(parseInt($(value).text())/1000)<=RaidEncounterFinSelected;
	}).closest("tr");
	
	RaidEncounterActeurs.each(function()
	{
		var acteur=$(this).html();

		var TmpLogs = RaidEncounterLogsSelected.filter(function (index,value) { return $(value).children("td.logSource").html()==acteur || $(value).children("td.logCible").html()==acteur })
		
		RaidEncounterActeursStats[acteur]={"Logs":TmpLogs,"apm":0,"dps":0,"hps":0};

		thorparse_CalculeAPM(acteur);
		thorparse_CalculeDPS(acteur);
		thorparse_CalculeHPS(acteur);
		
		if (refresh)
			thorparse_RefreshActeurView(acteur);
	});
	
	if (refresh)
	{
		thorparse_afficher_charts();
		thorparse_afficher_tables()
	}
}

function thorparse_CalculeAPM(acteur)
{
	var TmpsLogsCycle = RaidEncounterActeursStats[acteur]["Logs"].find("td.logEffet > span.SwtorElement > span.SwtorId").filter(
	function (index,value)
	{
		return $(value).html()=="836045448945479";
	}).closest("tr");
	
	RaidEncounterActeursStats[acteur]["LogsCycle"]=TmpsLogsCycle;
	RaidEncounterActeursStats[acteur]["apm"]= Math.floor(TmpsLogsCycle.length*6000/Math.max(RaidEncounterTimeLong,1))/100;
	RaidEncounterActeursStats[acteur]["CycleStat"]={};
	RaidEncounterActeursStats[acteur]["Cycle"]=[];
	
	TmpsLogsCycle.each(function (i,value)
	{
		var Ability    = $(value).find("td.logAction > span.SwtorElement");
		var AbilityId  = parseInt(Ability.children("span.SwtorId").text());
		var AbilityName= Ability.children("span.SwtorName").text();
		var TimeStamp  = parseInt($(value).children("td.logTime").text());
		
		var TempLine={"LogLine":value,"LastUse":0};
		
		if (AbilityId in RaidEncounterActeursStats[acteur]["CycleStat"])
		{
			var AbilityStats=RaidEncounterActeursStats[acteur]["CycleStat"][AbilityId];
			AbilityStats["NumberUse"]+=1;
			TempLine["LastUse"]=TimeStamp-AbilityStats["LastUse"];
			AbilityStats["SumDelay"]+=TempLine["LastUse"];
			AbilityStats["LastUse"]=TimeStamp;			
			
		}
		else
		{
			RaidEncounterActeursStats[acteur]["CycleStat"][AbilityId]=
				{ 
					"SwtorId":AbilityId,
					"SwtorName":AbilityName,
					"NumberUse":1,
					"SumDelay":0,
					"LastUse":TimeStamp
				};
		}
		
		RaidEncounterActeursStats[acteur]["Cycle"].push(TempLine);
	});
}

function thorparse_CalculeDPS(acteur)
{
	var TmpsLogsCycle = RaidEncounterActeursStats[acteur]["Logs"].filter(
	function (index,value)
	{
		return $(value).find("td.logEffet > span.SwtorElement > span.SwtorId").html()=="836045448945501"
		&& $(value).children("td.logSource").html()==acteur;
	}).closest("tr");
	
	RaidEncounterActeursStats[acteur]["LogsDamageDealt"]=TmpsLogsCycle;
	RaidEncounterActeursStats[acteur]["Damage"]=[];
	RaidEncounterActeursStats[acteur]["DpsOverTime"]=[];
	
	var TotalDamageDealt=0;
	
	TmpsLogsCycle.each(function (i,value)
	{	
		var secondIntoFight=Math.floor((parseInt($(value).children("td.logTime").text())-mRaidEncounterDebutSelected)/1000);
		var damageOutput   =parseInt($(value).find("td.logOutPut").text());
		
		TotalDamageDealt+=damageOutput;
		
		RaidEncounterActeursStats[acteur]["Damage"].push([secondIntoFight,damageOutput]);
		RaidEncounterActeursStats[acteur]["DpsOverTime"].push([secondIntoFight,Math.floor(TotalDamageDealt*100/Math.max(1,secondIntoFight))/100]);
	});
	
	RaidEncounterActeursStats[acteur]["DamageDealt"]= TotalDamageDealt;
	
	RaidEncounterActeursStats[acteur]["dps"]=Math.floor(RaidEncounterActeursStats[acteur]["DamageDealt"]*100/Math.max(RaidEncounterTimeLong,1))/100
}

function thorparse_CalculeHPS(acteur)
{
	var TmpsLogsCycle = RaidEncounterActeursStats[acteur]["Logs"].filter(
	function (index,value)
	{
		return $(value).find("td.logEffet > span.SwtorElement > span.SwtorId").html()=="836045448945500"
		&& $(value).children("td.logSource").html()==acteur;
	}).closest("tr");
	
	RaidEncounterActeursStats[acteur]["LogsHealingDone"]=TmpsLogsCycle;
	var TotalHealingDone=0;
	RaidEncounterActeursStats[acteur]["HpsOverTime"]=[];
	
	TmpsLogsCycle.each(function (i,value)
	{
		var secondIntoFight=Math.floor((parseInt($(value).children("td.logTime").text())-mRaidEncounterDebutSelected)/1000);
		TotalHealingDone+=parseInt($(value).find("td.logOutPut").text());
		RaidEncounterActeursStats[acteur]["HpsOverTime"].push([secondIntoFight,Math.floor(TotalHealingDone*100/Math.max(1,secondIntoFight))/100]);
	});
	RaidEncounterActeursStats[acteur]["HealingDone"]=TotalHealingDone;
	RaidEncounterActeursStats[acteur]["hps"]=Math.floor(TotalHealingDone*100/Math.max(RaidEncounterTimeLong,1))/100;
}

function thorparse_listen_range()
{
	$("#range").children("input.range").on('input',function (){
	
		var secondeInit = parseInt($(this).next("div").next("div").text());
	
		var secondeToAdd = parseInt($(this).val()) - secondeInit;
		
		$(this).next("div").next("div").text($(this).val());
		
		var time = thorparse_secondsToTime(thorparse_time_to_second($(this).next("div").text()) + secondeToAdd);
		
		$(this).next("div").children("span").html(function (i,origText)
		{ 
			if (i==0) return time.heures;
			else if (i==1) return time.minutes;
			else return time.secondes;
		});

	});
	
	ThorparseSandBox.find("input[type='submit']").click(function()
	{
		RaidEncounterMin = parseInt(ThorparseSandBox.find("div#min").text());
		RaidEncounterMax = parseInt(ThorparseSandBox.find("div#max").text());
		RaidEncounterTimeLong= RaidEncounterMax - RaidEncounterMin;
		mRaidEncounterDebutSelected=RaidEncounterDebutSelected*1000;
		
		thorparse_CalculeTout(true);
		thorparse_table_sort();
	});
}

function thorparse_RefreshActeurView(acteur)
{
	var ligne=ThorparseSandBox.children("#thorparseSummary").find("table tBody tr td.RaidEncounterActeur").filter(function(i,val)
	{
		
		return $(val).html()==acteur;
	}).closest("tr");
	
	ligne.children("td.RaidEncounterAPM").html(RaidEncounterActeursStats[acteur]["apm"]);
	ligne.children("td.RaidEncounterDPS").html(RaidEncounterActeursStats[acteur]["dps"]);
	ligne.children("td.RaidEncounterHPS").html(RaidEncounterActeursStats[acteur]["hps"]);
	ligne.children("td.RaidEncounterDamageDealt").html(RaidEncounterActeursStats[acteur]["DamageDealt"]);
	ligne.children("td.RaidEncounterHealingDone").html(RaidEncounterActeursStats[acteur]["HealingDone"]);
}

function thorparse_afficher_tableau_de_bord()
{
	var time_debut = thorparse_secondsToTime(RaidEncounterDebut);
	var time_fin = thorparse_secondsToTime(RaidEncounterFin);
	
	var s_deb = time_debut.secondes;
	var m_deb = time_debut.minutes;
	var h_deb = time_debut.heures;
	var s_fin = time_fin.secondes;
	var m_fin = time_fin.minutes;
	var h_fin = time_fin.heures;

	ThorparseSandBox.prepend(
				"<div id=\"thorparseDashBoard\">"
				+"<h3 class=\"page-header\">Tableau de bord</h3>"
				+"<div id=\"range\" class=\"well\">"
				+"<input class='range' type='range' size='300' min='"+RaidEncounterMin+"' max='"+RaidEncounterMax+"' step='1' value='0' />\n"
				+"<div>\<span id='min_hour'>"+h_deb+"</span>:<span id='min_minute'>"+m_deb+"</span>:<span id='min_second'>"+s_deb+"</span></div>\n"
				+" / <div id='min'>0</div> s\n"
				+"<br/>\n"
				+"<input class='range' type='range' min='"+RaidEncounterMin+"' max='"+RaidEncounterMax+"' step='1' value='"+RaidEncounterMax+"' />\n"
				+"<div><span id='max_hour'>"+h_fin+"</span>:<span id='max_minute'>"+m_fin+"</span>:<span id='max_second'>"+s_fin+"</span></div>\n"
				+" / <div id='max'>"+RaidEncounterMax+"</div>\n s </div>"
				+"</div>");
	
	$("#range").append("<br/><p class=\"text-center\"><input class=\"btn btn-default\" type='submit' value='GO' /></p>");
}


function thorparse_afficher_summary()
{
	
	ThorparseSandBox.append("<div id=\"thorparseSummary\">\n<h3 class=\"sub-header\">Resume</h3>\n<table class=\"table\"></table>\n</div>");
	var ThorparseSummary=ThorparseSandBox.children("#thorparseSummary").children("table");
	ThorparseSummary.append(
	"<thead>\n"
	+"\t<tr>\n"
		+"\t\t<th>Personnage</th>\n"
		+"\t\t<th>APM</th>\n"
		+"\t\t<th>DPS</th>\n"
		+"\t\t<th>HPS</th>\n"
		+"\t\t<th>Damage Dealt</th>\n"
		+"\t\t<th>Healing Done</th>\n"
	+"\t</tr>\n"
	+"</thead>\n"
	+"<tBody></tBody>\n");

	RaidEncounterActeurs.each(function(){
		var acteurName=$(this).html();
	
		ThorparseSummary.children("tbody").append(
		"<tr>"
		+"<td class=\"RaidEncounterActeur\">"+acteurName+"</td>"
		+"<td class=\"RaidEncounterAPM\">"+RaidEncounterActeursStats[acteurName]["apm"]+"</td>"
		+"<td class=\"RaidEncounterDPS\">"+RaidEncounterActeursStats[acteurName]["dps"]+"</td>"
		+"<td class=\"RaidEncounterHPS\">"+RaidEncounterActeursStats[acteurName]["hps"]+"</td>"
		+"<td class=\"RaidEncounterDamageDealt\">"+RaidEncounterActeursStats[acteurName]["DamageDealt"]+"</td>"
		+"<td class=\"RaidEncounterHealingDone\">"+RaidEncounterActeursStats[acteurName]["HealingDone"]+"</td>"
		+"</tr>");
		});
		
	ThorparseSandBox.append("<div id=\"dpsOverTimeChart\" style=\"width:100%; height:400px;\"></div>");
	ThorparseSandBox.append("<div id=\"hpsOverTimeChart\" style=\"width:100%; height:400px;\"></div>");
	ThorparseSandBox.append("<div id=\"RaidTables\"></div>");
}

function thorparse_afficher_charts()
{
	var DpsOverTimeSeries=[];
	var HpsOverTimeSeries=[];
	
	RaidEncounterActeurs.each(function()
	{
		var acteurName=$(this).text();
		
		DpsOverTimeSeries.push({ name:acteurName,data:RaidEncounterActeursStats[acteurName]["DpsOverTime"]});
		HpsOverTimeSeries.push({ name:acteurName,data:RaidEncounterActeursStats[acteurName]["HpsOverTime"]});
	});

	$(function () {
			$('#dpsOverTimeChart').highcharts({
				chart: {
					type: 'line'
				},
				title: {
					text: 'DPS Over Time',
					x: -20 //center
				},
				xAxis: {
					type:'millisecond'
				},
				yAxis: {
					title: {
						text: 'DPS'
					}
				},
				tooltip: {
					valueSuffix: ' DPS',
					shared: true,
					crosshairs: true
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle',
					borderWidth: 0
				},
				series: DpsOverTimeSeries
			});
		});
		
	$(function () {
			$('#hpsOverTimeChart').highcharts({
				chart: {
					type: 'line'
				},
				title: {
					text: 'HPS Over Time',
					x: -20 //center
				},
				xAxis: {
					type:'millisecond'
				},
				yAxis: {
					title: {
						text: 'HPS'
					}
				},
				tooltip: {
					valueSuffix: ' HPS',
					shared: true,
					crosshairs: true
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle',
					borderWidth: 0
				},
				series: HpsOverTimeSeries
			});
		});
}

function thorparse_afficher_tables()
{
	var TablesDiv=$('#RaidTables');

	TablesDiv.html("");
	
	RaidEncounterActeurs.each(function(acteurId)
	{
		var acteurName=$(this).text();
		var CycleStat=RaidEncounterActeursStats[acteurName]["CycleStat"];
		var Cycle=RaidEncounterActeursStats[acteurName]["Cycle"];
		
		TablesDiv.append("<div id=\"RaidActeur_"+acteurId+"\"></div>");
		
		var ActeurDiv=$(TablesDiv.children("div#RaidActeur_"+acteurId));
		
		ActeurDiv.append("<h4 class=\"sub-header\">"+acteurName+"</h4>");
		
		ActeurDiv.append("<table class=\"table CycleSumm sortable\"></table>");
		
		var TableCycleSumm = ActeurDiv.children("table.CycleSumm");
		
		TableCycleSumm.append("<thead>"
			+"<tr>"
			+"<th>Pouvoir</th>"
			+"<th class=\"sortInt text-center\">#</th>"
			+"<th class=\"sortInt text-center\">delai moyen</th>"
			+"<th class=\"sortInt text-center\">#/min</th>"
			+"</tr>"
			+"<\thead>");
		
		for (var AbilityId in CycleStat)
		{
			TableCycleSumm.append(
			"<tr>"
			+"<td>"+CycleStat[AbilityId]["SwtorName"]+"</td>"
			+"<td class=\"text-right\">"+CycleStat[AbilityId]["NumberUse"]+"</td>"
			+"<td class=\"text-right\">"+Math.floor(CycleStat[AbilityId]["SumDelay"]/CycleStat[AbilityId]["NumberUse"])/1000+"</td>"
			+"<td class=\"text-right\">"+Math.floor(CycleStat[AbilityId]["NumberUse"]*6000/RaidEncounterTimeLong)/100+"</td>"
			+"</tr>");
		}
		
		
		ActeurDiv.append("<table class=\"Cycle table table-striped\"></table>");
		
		var TableCycle = ActeurDiv.children("table.Cycle");
		
		TableCycle.append("\t<thead>\n"
			+"\t<tr>\n"
			+"\t\t<th>Heure</th>\n"
			+"\t\t<th>Durée</th>\n"
			+"\t\t<th>Pouvoir</th>\n"
			+"\t\t<th>Dernière utilisation</th>\n"
			+"\t</tr>\n"
			+"\t<\thead><tbody></tbody>\n");
		
		for (var i in Cycle)
		{
			AbilityLine=$(Cycle[i]["LogLine"]);
			var Time=parseInt(AbilityLine.find("td.logTime").text());
			var Duree=Math.floor((Time-RaidEncounterDebutm)/100)/10;
			var AbilityId=parseInt(AbilityLine.find("td.logAction > span.SwtorElement > span.SwtorId").text());
			
			var Name=CycleStat[AbilityId]["SwtorName"];
			
			var TimeEnt=Math.floor(Time/1000);
			var TimeFra=Math.floor(((Time/1000)-TimeEnt)*1000);
			
			Time = thorparse_secondsToTime(TimeEnt);
			Time = Time.heures+":"+Time.minutes+":"+Time.secondes+"."+TimeFra;
		
			TableCycle.children("tbody").append(
			"\t<tr>\n"
			+"\t\t<td>"+Time+"</td>\n"
			+"\t\t<td>"+Duree+"</td>\n"
			+"\t\t<td>"+Name+"</td>\n"
			+"\t\t<td>"+Math.floor(Cycle[i]["LastUse"]/100)/10+"</td>\n"
			+"\t</tr>\n");
		}
		
	});
}

Number.prototype.mod = function(n) 
{
	return ((this%n)+n)%n;
}

function thorparse_secondsToTime(newVal)
{
	var seconde_calc = newVal.mod(60);

	var seconde = (seconde_calc>=10) ? seconde_calc : "0"+seconde_calc;

	var minute_calc = Math.floor(newVal/60).mod(60);	

	var minute = (minute_calc>10) ? minute_calc : "0"+minute_calc;

	var hour_calc = (Math.floor(newVal/3600) ).mod(24);	
	
	var hour = (hour_calc>10) ? hour_calc : "0"+hour_calc;
	
	return { heures:hour, minutes:minute, secondes:seconde };
}

function thorparse_time_to_second(str)
{
	var res = str.split(":");
	return parseInt(res[0])*3600+parseInt(res[1])*60+parseInt(res[2]);
}

function thorparse_table_sort()
{
	var n=1;
	var tri_text=new Array()
	,tri_int = new Array();
	var table_id= true;
	var tri = null ;
	tri_text[0]= function(a, b)	{ return $(a).text() > $(b).text() ? 1 : -1; };
	tri_text[1]= function(a, b)	{ return $(a).text() < $(b).text() ? 1 : -1; };
	tri_int[0] = function(a, b)	{ return parseInt($(a).text()) > parseInt($(b).text()) ? 1 : -1; };
	tri_int[1] = function(a, b)	{ return parseInt($(a).text()) < parseInt($(b).text()) ? 1 : -1; };
	
	
	$("table.sortable thead tr th").click(function () 
	{	
		if ($(this).hasClass("sortInt"))
		{
			tri = tri_int[n];
		}
		else
		{
			tri = tri_text[n];
		}

		$(this).closest("table").children("tbody").children("tr").children("td:nth-of-type("+($(this).closest("th").index()+1)+")").sortElements(
			tri,
			function()
			{
				return this.parentNode; 
			}
		);
		
		n = $(this).closest("table").attr('id')==table_id || table_id ? (n+1)%2 : n;
		
		table_id = $(this).closest("table").attr('id');
	});

}

jQuery.fn.reduce = function(valueInitial, fnReduce)
{
	jQuery.each( this , function(i, value)
	{
		valueInitial = fnReduce.apply(value, [valueInitial, i, value]);
	});
	return valueInitial;
}


/**
 * jQuery.fn.sortElements
 * --------------
 * @param Function comparator:
 *   Exactly the same behaviour as [1,2,3].sort(comparator)
 *   
 * @param Function getSortable
 *   A function that should return the element that is
 *   to be sorted. The comparator will run on the
 *   current collection, but you may want the actual
 *   resulting sort to occur on a parent or another
 *   associated element.
 *   
 *   E.g. $('td').sortElements(comparator, function(){
 *      return this.parentNode; 
 *   })
 *   
 *   The <td>'s parent (<tr>) will be sorted instead
 *   of the <td> itself.
 */
jQuery.fn.sortElements = (function(){
 
    var sort = [].sort;
 
    return function(comparator, getSortable) {
 
        getSortable = getSortable || function(){return this;};
 
 
        var placements = this.map(function(){
	
		console.log(getSortable.call(this));
	
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
				
				
                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
				
 
            return function() {
 
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
 
                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);
 
            };
 
        });
 
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
 
    };
 
})();