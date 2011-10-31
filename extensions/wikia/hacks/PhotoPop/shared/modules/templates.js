var exports = exports || {};

define.call(exports, {
	wrapper:
		"<div id='PhotoPopWrapper'>\
			<div id='homeScreen' class='screen'>\
				<div id='logoWrapper'>\
					<img id='logoPhotopop' src='{{#image}}PHOTOPOP_LOGO{{/image}}'/><br/>\
					<img id='logoWikia' src='{{#image}}POWERED_BY_LOGO{{/image}}'>\
				</div>\
				<div id='sliderWrapper' class='composite transition-all'>\
					<div id='buttonWrapper'>\
						<div id='button_scores'>\
							<img src='{{#image}}buttonSrc_scores{{/image}}'/>\
						</div>\
						<div id='button_tutorial'>\
							<img src='{{#image}}buttonSrc_tutorial{{/image}}'/>\
						</div>\
						<div id='button_volume'>\
							<img class='on' src='{{#image}}buttonSrc_volumeOn{{/image}}'/>\
							<img class='off' src='{{#image}}buttonSrc_volumeOff{{/image}}'/>\
						</div>\
					</div>\
					<div id='sliderContent' data-scroll='x'>\
						<span class='progress'>"+Wikia.i18n.Msg('photopop-game-loading')+"</span>\
					</div>\
				</div>\
			</div>\
			<div id='highscoreScreen' class='screen'>\
				<h1>"+Wikia.i18n.Msg('photopop-game-highscores')+"</h1>\
				<table><tr>\
				<th> </th>\
				<th>"+Wikia.i18n.Msg('photopop-game-wiki')+"</th>\
				<th>"+Wikia.i18n.Msg('photopop-game-date')+"</th>\
				<th>"+Wikia.i18n.Msg('photopop-game-score')+"</th>\
				<tr></table>\
				<div id='goBack'>\
					<img src='{{#image}}buttonSrc_home{{/image}}'/>\
				</div>\</div>\
			<div id='gameScreen' class='screen'>\
				<div id='scoreBarWrapper'>\
				<div id='scoreBar'></div>\
			</div>\
			<div id='bgWrapper'>\
				<div id='bgPic'><img/></div>\
			</div>\
			<div id='gameBoard'>\
				<div id='endGameOuterWrapper'>\
					<div id='endGameInnerWrapper'>\
						<div id='highScore'>\
							"+Wikia.i18n.Msg('photopop-game-highscore')+": <span>0</span>\
						</div>\
						<div id='summaryWrapper'>\
							<div id='endGameSummary'>\
								<h1>Finished</h1>\
								<div class='summaryTextWrapper'>\
									<div class='summaryText_completion'></div>\
									<div class='summaryText_score'></div>\
								</div>\
							</div>\
							<div id='endGameButtons'>\
								<img id='playAgain' src='{{#image}}buttonSrc_endReplay{{/image}}'/>\
								<img id='goHome' src='{{#image}}buttonSrc_endHome{{/image}}'/>\
								<img id='goToHighScores' src='{{#image}}buttonSrc_endScores{{/image}}'/>\
							</div>\
						</div>\
					</div>\
				</div>\
				<div id='timeUpText'>"+Wikia.i18n.Msg('photopop-game-timeup')+"</div>\
				<div id='continue' class='bottomBar'>\
					<span id='continueText'></span>\
					<img src='{{#image}}buttonSrc_contiunue{{/image}}'/>\
				</div>\
				<div id='answerDrawer'>\
					<div id='answerButton' class='closed'>\
						<img src='{{#image}}buttonSrc_answerOpen{{/image}}' />\
						<img src='{{#image}}buttonSrc_answerClose{{/image}}' />\
					</div>\
					<ul id='answerList'>\
						<li id='answer0'></li>\
						<li id='answer1'></li>\
						<li id='answer2'></li>\
						<li id='answer3'></li>\
					</ul>\
				</div>\
				<div id='hud' class='bottomBar'>\
					<div id='home'>\
						<img src='{{#image}}buttonSrc_home{{/image}}'/>\
					</div>\
					<div id='score'>\
						"+Wikia.i18n.Msg('photopop-game-points')+": <span id='roundPoints'>0</span> "+Wikia.i18n.Msg('photopop-game-total')+": <span id='totalPoints'>0</span>\
					</div>\
					<div id='muteButton'>\
						<img src='{{#image}}buttonSrc_gameMute{{/image}}'>\
						<img src='{{#image}}buttonSrc_gameUnmute{{/image}}'>\</div>\
					<div id='pauseButton'>\
						<img src='{{#image}}buttonSrc_resume{{/image}}'>\
						<img src='{{#image}}buttonSrc_pause{{/image}}'>\
					</div>\
					<div id='progress'>\
						Progress: <span>0/0</span>\
					</div>\
				</div>\
				<table id='tilesWrapper'></table>\
				<div id='modalWrapper'><div id='modal'></div></div>\
			</div>\
			</div>\
		</div>",
	
	gameSelector:
		"<ul id='gamesList'>{{#games}}\
			<li class='gameIcon {{#round}}resumeGame{{/round}}' data-id='{{id}}'>\
				<img src='{{thumbnail}}'></img>\
				<div class='gameName'>\
					{{name}}\
				</div>\
			</li>\
		{{/games}}</ul>"
});
