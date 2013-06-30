(function(){
	
	DestinyTMI = {
		ui: null,
		overlay: null,
		champSelector: null,
		champSearch: null,
		teamSelection: null,
		enabled: true,
		team: null,
		teamPersistedChampions: null,
		onshow: null,
		onconfirm: null,
		onsave: null,
		settings: null,
		lockedState: false,
		unlockedChampions: null,
		toolsUI: null,
		progressModal: null,
		teamSelection: null,
		teamCurrency: null,
		
		show: function(options){
			var self = this;
			options = $.extend({team: null, champions: []}, options);
			self.team = $.extend({}, options.team);
			self.teamPersistedChampions = $.extend({}, options.champions);
			self.settings = self.ui.data('settings');
			self.onconfirm = options.onconfirm;
			self.onsave = options.onsave;
			self.onshow = options.onshow;
			self.showOverlay();
			new DestinyFeedConsumer({
				url: destiny.baseUrl + 'Fantasy/Champions.json',
				success: function(championsResponse, textStatus){
					self.setupChampions(championsResponse);
					self.enableChampions();
					self.resetChampionSelection();
					self.setupChampionSlots(self.teamPersistedChampions);
					self.updateTeamCreditUi();
					self.showOverlay();
					self.ui.show();
					self.onshow.call(self);
				}
			});
		},
		
		error: function(e){
			var consoleUi = this.ui.find('.team-maker-console').html(e).show();
			window.clearTimeout(consoleUi.data('hideTimeout'));
			consoleUi.data('hideTimeout', window.setTimeout(function(){
				consoleUi.fadeOut(300);
			}, 2000));
		},
		
		showOverlay: function(){
			var self = this;
			self.overlay.detach().appendTo('body');
			self.ui.css('top','0');
			$(window).off('resize.tmoverlay').on('resize.tmoverlay', function(){
				self.sizeOverlay();
			});
			self.sizeOverlay();
		},
		
		sizeOverlay: function(){
			var self = this;
			self.overlay.width($('body').outerWidth()).height($('body').outerHeight()).show();
			self.positionTeamMaker();
		},
		
		positionTeamMaker: function(){
			var self = this,
				top = (((parseInt($(window).height()) / 2) + $(window).scrollTop()) - parseInt(self.ui.height()) / 2),
				left = ((parseInt($(window).width()) / 2) - parseInt(self.ui.width()) / 2);
			self.ui.css({
				top: ((top > 40) ? top : 40 ) + 'px',
				left: left + 'px'
			});
		},
		
		hide: function(){
			$(window).off('resize.tmoverlay');
			$(window).off('scroll.tmui');
			this.ui.hide();
			this.overlay.hide();
		},
		
		resetChampionSelection: function(){
			this.champSelector.find('.champion')
			.addClass('champion-locked')
			.removeClass('champion-unlocked');
		},
		
		setupChampions: function(champions){
			var teamChampionSelector = this.ui.find('.team-maker-selection-inner').empty();
			for(var x=0;x<champions.length;x++){
				var champUi = $(			
					'<div data-value="'+ champions[x].championValue +'" data-id="'+ champions[x].championId +'" data-name="'+ htmlEncode(champions[x].championName) +'" class="champion'+((champions[x].championFree == '1') ? ' champion-free':' champion-locked')+'" style="float:left; width: 10%;">'+
						'<div class="clearfix">'+
							'<div class="thumbnail" title="'+ htmlEncode(champions[x].championName) +'">'+
								'<img style="max-width:100%;" src="'+ destiny.cdn +'/web/img/320x320.gif" data-src="'+ getChampIcon(champions[x].championName) +'" />'+
								'<span class="championMultiplier" title="Score Multiplier"><i class="icon-star icon-white subtle"></i>'+Math.round(champions[x].championMultiplier*100)+'%</span>'+
								'<div class="champion-values clearfix">'+
									'<span class="championValue"><i class="icon-money"></i>'+ champions[x].championValue +'</span>'+
									'<span class="championUnlocked">Unlocked</span>'+
									'<span class="championFree">Free</span>'+
								'</div>'+
							'</div>'+
						'</div>'+
					'</div>').data('champion', champions[x]);
				teamChampionSelector.append(champUi);
			};
			DestinyTMI.ui.find('.champ-search > input').removeAttr('disabled').val('');
			DestinyTMI.ui.find('.champ-filter.active').removeClass('active').addClass('btn-inverse');
			DestinyTMI.ui.removeClass('filter-owned filter-free');
		},
		
		setUnlockedChampions: function(champions){
			this.unlockedChampions = champions;
			for(var i=0;i<this.unlockedChampions.length;++i){
				this.unlockChampion(this.unlockedChampions[i]);
			};
		},
		
		unlockChampion: function(champ){
			this.champSelector.find('.champion[data-id="'+ champ.championId +'"]').removeClass('champion-locked').addClass('champion-unlocked');
		},
		
		getChampions: function(){
			var champions = [], slots = this.teamSelection.find('.champion-slot-full');
			for(var i=0;i<slots.length;++i){
				champions.push($(slots.get(i)).data('champion'));
			};
			return champions;
		},
		
		getChampionIds: function(){
			var champions = [], slots = this.teamSelection.find('.champion-slot-full');
			for(var i=0;i<slots.length;++i){
				champions.push($(slots.get(i)).data('champion').championId);
			};
			return champions;
		},
		
		setupChampionSlots: function(champions){
			var self = this;
			self.ui.find('.champion-slot').each(function(index){
				var slot = $(this), 
					champ = champions[index];
				self.resetSlotState(slot);
				if(champ != undefined && (champ.unlocked == 1 || champ.championFree == 1)){
					self.convertFromEmptySlot(slot, champ);
					self.disableChampion(champ);
				}else{
					self.convertToEmptySlot(slot);
				};
			});
		},
		
		updateTeamCreditUi: function(){
			var self = this;
			self.teamCurrency.find('.credits').text(Math.floor(self.team.credits));
			self.teamCurrency.find('.transfers').text(self.team.transfersRemaining);
		},
		
		enableChampions: function(){
			this.champSelector.find('.champion.disabled').removeClass('disabled');
		},
		
		enableChampion: function(champ){
			this.champSelector.find('.champion[data-name="'+ champ.championName +'"]').removeClass('disabled');
		},
		
		disableChampion: function(champ){
			this.champSelector.find('.champion[data-name="'+ champ.championName +'"]').addClass('disabled');
		},
		
		convertFromEmptySlot: function(slot, champ){
			slot.removeClass('champion-slot-empty').addClass('champion-slot-full').data('champion', champ).attr('data-championid', champ.championId);
			slot.find('.thumbnail').attr('title', champ.championName);
			slot.find('img').attr('src', getChampIcon(champ.championName));
		},
		
		convertToEmptySlot: function(slot){
			slot.removeClass('champion-slot-full').addClass('champion-slot-empty').data('champion', null).removeAttr('data-championid');
			var img = slot.find('img');
			if(slot.hasClass('champion-transfer')){
				img.attr('src', getChampIcon(slot.data('transferOut').championName));
			}else{
				img.attr('src', destiny.cdn+'/web/img/320x320.gif');
			}
		},
		
		resetSlotState: function(slot){
			slot.removeClass('champion-transfer').data('transferOut', null).find('.icon-transfer-in,.icon-transfer-out').remove();
		},
		
		getFreeChampSlot: function(champ){
			if(champ != undefined){
				var slot = this.ui.find('.champion-slot-empty[data-transfer-championid="'+champ.championId+'"]');
				if(slot.get(0) != null){
					return slot;				
				}
			}
			return this.ui.find('.champion-slot-empty:first');
		},
		
		removeChampions: function(){
			var slots = this.teamSelection.find('.champion-slot-full'), self = this;
			slots.each(function(){
				self.removeChampion($(this).data('champion'), $(this));
			});
		},
		
		addTransferIcon: function(slot,  direction){
			this.removeTransferIcon(slot);
			if(direction == 'out'){
				slot.append('<i title="Transfer Out" class="icon-transfer icon-transfer-out"></i>');
			}else{
				slot.append('<i title="Transfer In" class="icon-transfer icon-transfer-in"></i>');
			}
		},
		
		removeTransferIcon: function(slot){
			slot.find('.icon-transfer').remove();
		},
		
		removeChampion: function(champ, slot){
			var self = this;
			if(slot == undefined || slot == null){
				slot = self.teamSelection.find('.champion-slot-full[data-championid="'+champ.championId+'"]');
			};
			if(slot.get(0) != null){
				try{
					self.checkRemoveRestrictions(champ);
					// Check and set transfer out state
					if(slot.hasClass('champion-transfer')){
						self.addTransferIcon(slot, 'out');
					}else{
						for(var x in self.teamPersistedChampions){
							if(self.teamPersistedChampions[x].championId == champ.championId){
								// Transfer out
								slot.addClass('champion-transfer').data('transferOut', champ).attr('data-transfer-championid', champ.championId);
								self.addTransferIcon(slot, 'out');
								self.team.transfersRemaining--;
								break;
							}
						}
					}
					self.convertToEmptySlot(slot);
					self.updateTeamCreditUi();
					self.enableChampion(champ);
				}catch(e){
					self.handleResponseError(e);
				}
			};
		},
		
		hasFreeSlot: function(champ){
			return (this.getFreeChampSlot(champ).get(0) != null) ? true:false;
		},
		
		addChampion: function(champ){
			var self = this,
				slot = self.getFreeChampSlot(champ);
			if(slot.get(0) != null){
				try{
					self.checkAddRestrictions(champ);
					//Check transfers
					if(slot.hasClass('champion-transfer')){
						//Trasfer reset
						self.removeTransferIcon(slot);
						if(slot.data('transferOut') != null && slot.data('transferOut').championId == champ.championId){
							champ = slot.data('transferOut');
							slot.removeClass('champion-transfer').removeAttr('data-transfer-championid').data('transferOut', null);
							self.team.transfersRemaining++;
						}else{
							self.addTransferIcon(slot, 'in');
						}
					};
					self.convertFromEmptySlot(slot, champ);
					self.disableChampion(champ);
					self.updateTeamCreditUi();
				}catch(e){
					self.error(e);
				}
			}else{
				self.error('No space available');
			}
		},
		
		addFreeChampion: function(champ){
			var self = this;
			if(!self.hasFreeSlot(champ)){
				if(parseInt(self.team.credits)-parseInt(champ.championValue) < 0){
					self.error('No space available and insufficient credits');
				}else{
					self.beginChampionPurchase(champ);
				}
				return;
			};
			self.setProgress(
				'<div>'+
					'<span><i class="icon-money"></i>'+ champ.championValue +'</span>'+
					'&nbsp;<img src="'+ getChampIcon(champ.championName) +'" style="width:32px; height:32px;" />'+
				'</div>'+
				'<p class="team-maker-popup">'+
					'Free champions incur a <span style="color:red">-'+ Math.floor(self.settings.freeMultiplierPenalty*100) +'%</span> score penalty and are rotated regularly.'+
					'<br />Add to team anyway?'+
					((parseInt(self.team.credits)-parseInt(champ.championValue) < 0) ? '' : 
					' or <a title="Purchase" href="#" class="purchase-link">Purchase '+htmlEncode(champ.championName)+'</a>.')+
				'</p>'+
				'<div>'+
					'<button class="btn btn-add">Add</button>&nbsp;'+
					'<button class="btn btn-danger">Cancel</button>'+
				'</div>'
			);
			self.progressModal.find('button.btn-add').one('click', function(){
				self.hideProgress();
				if(self.hasFreeSlot(champ)){
					self.addChampion(champ);
				};
				return false;
			});
			self.progressModal.find('button.btn-danger').one('click', function(){
				self.hideProgress();
				return false;
			});
			self.progressModal.find('.purchase-link').one('click', function(){
				self.hideProgress();
				self.beginChampionPurchase(champ);
				return false;
			});
		},
		
		beginChampionPurchase: function(champ){
			var self = this;
			if(parseInt(self.team.credits)-parseInt(champ.championValue) < 0){
				return self.error('Insufficient credits');
			}
			self.setProgress(
				'<div>'+
					'<span><i class="icon-money"></i>'+ champ.championValue +'</span>'+
					'&nbsp;<img src="'+ getChampIcon(champ.championName) +'" style="width:32px; height:32px;" />'+
				'</div>'+
				'<p class="team-maker-popup">'+
					'Purchase <strong>'+htmlEncode(champ.championName)+'</strong>?<br />This cannot be undone.'+
				'</p>'+
				'<div>'+
					'<button class="btn btn-primary">Purchase</button>&nbsp;'+
					'<button class="btn btn-danger">Cancel</button>'+
				'</div>'
			);
			self.progressModal.find('button.btn-primary').one('click', function(){
				self.hideProgress();
				self.purchaseChampion(champ);
				
			});
			self.progressModal.find('button.btn-danger').one('click', function(){
				self.hideProgress();
			});
		},
		
		purchaseChampion: function(champ){
			var self = this;
			self.lockedState = true;
			self.setProgress('<span class="busy"><img src="'+ getChampIcon(champ.championName) +'" style="width:20px; height:20px;" /> Purchasing <strong>'+htmlEncode(champ.championName)+'</strong>...</span>');
			$.ajax({
				url: destiny.baseUrl + 'Fantasy/Champion/Purchase.json',
				data: {
					championId: champ.championId,
					teamId: self.team.teamId
				},
				success: function(response){
					if(typeof response == 'object' && response.success){
						self.setProgress('<span><i class="icon-ok icon-white subtle"></i> Purchase successful</span>');
						self.unlockChampion(champ);
						self.team.credits = parseInt(self.team.credits)-parseInt(champ.championValue);
						self.updateTeamCreditUi();
						self.onsave.call(self, self.team);
						if(self.hasFreeSlot(champ)){
							self.addChampion(champ);
						};
						window.setTimeout(function(){
							self.hideProgress();
							self.lockedState = false;
						}, 1000);
					}else{
						self.handleResponseError(response.message);
					}
				},
				error: function(e){
					self.handleResponseError(e);
				}
			});
		},
		
		checkRemoveRestrictions: function(champ){
			if(champ.teamId != undefined && champ.teamId != 0){
				if(this.team.transfersRemaining <= 0){
					throw 'Insufficient transfers';
				}
			}
		},
		
		checkAddRestrictions: function(champ){
			var self = this;
			// Check if the champ is already in the list.
			self.ui.find('.champion-slot-full').each(function(){
				var slotChamp = $(this).data('champion');
				if(slotChamp != null && slotChamp.championId == champ.championId){
					throw 'Champion already in team';
				};
			});
		},
		
		disableSelection: function(){
			this.teamCurrency.addClass('disabled');
			this.champSelector.addClass('disabled');
			this.champSearch.attr('disabled', 'disabled');
			this.teamSelection.addClass('disabled');
			this.enabled = false;
		},
		
		enableSelection: function(){
			this.teamCurrency.removeClass('disabled');
			this.champSelector.removeClass('disabled');
			this.champSearch.removeAttr('disabled', 'disabled');
			this.teamSelection.removeClass('disabled');
			this.enabled = true;
		},
		
		cancelPick: function(){
			this.hide();
		},
		
		confirmPick: function(){
			try{
				this.onconfirm.call(this);
				this.persistTeam();
			}catch(e){
				this.error(e);
			}
		},
		
		handleResponseError: function(e){
			var self = this;
			self.setProgress('<span><i class="icon-remove icon-white subtle"></i> Error: '+e+'</span>');
			window.setTimeout(function(){
				self.hideProgress();
				self.lockedState = false;
			}, 2000);
		},
		
		persistTeam: function(){
			var self = this;
			self.lockedState = true;
			self.setProgress('<span class="busy"><i class="icon-hdd icon-white subtle"></i> Updating team...</span>');
			$.ajax({
				url: destiny.baseUrl + 'fantasy/team/update.json',
				type: 'POST',
				data: {teamId: this.team.teamId, champions: this.getChampionIds().join(',')},
				success: function(response){
					if(typeof response != 'object' || response.success == false){
						return self.handleResponseError(response.message);
					}
					window.setTimeout(function(){
						// Team was saved, update team maker with new team and champs
						if(response.data != undefined && response.data.team != undefined){
							self.team = response.data.team;
							self.updateTeamCreditUi();
							self.setupChampionSlots(response.data.champions);
							// Also update team bar.
							self.onsave.call(this, response.data.team, response.data.champions);
						};
						self.setProgress('<span><i class="icon-ok icon-white subtle"></i> Update successful</span>');
						window.setTimeout(function(){
							self.hideProgress();
							self.lockedState = false;
							self.hide();
						}, 1000);
					}, 1000);
				},
				error: function(e){
					self.handleResponseError(e);
				}
			});
		},
		
		setProgress: function(html, start, time){
			DestinyTMI.disableSelection();
			DestinyTMI.toolsUI.attr('disabled', 'disabled');
			DestinyTMI.progressModal.html(html).css({
				left: ((parseInt(DestinyTMI.ui.width()) / 2) - parseInt(DestinyTMI.progressModal.width()) / 2) + 'px',
				top: ((parseInt(DestinyTMI.ui.height()) / 2) - parseInt(DestinyTMI.progressModal.height()) / 2) + 'px'
			}).show();
			DestinyTMI.innerOverlay.css({
				width: parseInt(DestinyTMI.ui.width()) + 'px',
				height: parseInt(DestinyTMI.ui.height()) + 'px'
			}).show();
		},
		
		hideProgress: function(){
			DestinyTMI.enableSelection();
			DestinyTMI.progressModal.empty().hide();
			DestinyTMI.innerOverlay.hide();
			DestinyTMI.toolsUI.find('button').removeAttr('disabled');
		}
		
	};

	DestinyTMI.ui = $('.team-maker');
	DestinyTMI.overlay = $('.team-maker-overlay');
	DestinyTMI.innerOverlay = $('.team-maker-inner-overlay');
	DestinyTMI.toolsUI = DestinyTMI.ui.find('.team-maker-tools');
	DestinyTMI.progressModal = DestinyTMI.ui.find('.team-maker-progress');
	DestinyTMI.teamSelection = DestinyTMI.ui.find('.team-maker-slots');
	DestinyTMI.teamCurrency = DestinyTMI.ui.find('.team-maker-currency');
	DestinyTMI.champSelector = DestinyTMI.ui.find('.team-maker-selection');
	DestinyTMI.champSearch = DestinyTMI.ui.find('.champ-search input[type="text"]');
	DestinyTMI.champSearch.typeahead({
		source: DestinyTMI.champSearch.data('source'),
		sorter: function(items){
			DestinyTMI.ui.find('.champion').addClass('hidden');
			for(var i=0;i<items.length;i++){
				DestinyTMI.ui.find('.champion[data-name="'+items[i]+'"]').removeClass('hidden');
			};
			return items;
		},
		updater: function (item) {
			DestinyTMI.ui.find('.champion').addClass('hidden');
			DestinyTMI.ui.find('.champion[data-name="'+item+'"]').removeClass('hidden');
			return item;
		}
	});

	DestinyTMI.ui.find('.champ-sorting .dropdown-menu a').on('click',  function(){
		var sortby = $(this).data('by');
		if(sortby == '' || !sortby){
			return false;
		}
		DestinyTMI.champSelector.find('.champion').sortElements(function(a, b){
			var aV = $(a).data(sortby), 
				bV = $(b).data(sortby);
			if(aV > bV) {
				return 1;
			} else if(aV < bV) {
				return -1;
			} else {
				return 0;
			}
		});
	});
	
	DestinyTMI.ui.find('.champ-filter').on('click',  function(){
		if(!$(this).hasClass('active')){
			$(this).removeClass('btn-inverse');
			DestinyTMI.ui.addClass('filter-' + $(this).data('filterby'));
		}else{
			$(this).addClass('btn-inverse');
			DestinyTMI.ui.removeClass('filter-' + $(this).data('filterby'));
		}
	});
	
	DestinyTMI.champSearch.on('keyup', function(){
		if($(this).val() == ''){
			DestinyTMI.ui.find('.champion').removeClass('hidden');
		}
	});
	
	DestinyTMI.teamSelection.on('click', '.champion-slot-full', function(){
		if(!DestinyTMI.enabled || DestinyTMI.lockedState) return;
		DestinyTMI.removeChampion($(this).data('champion'));
		return false;
	});
	
	DestinyTMI.teamSelection.on('click', '.champion-slot.champion-transfer.champion-slot-empty', function(){
		if(!DestinyTMI.enabled || DestinyTMI.lockedState) return;
		DestinyTMI.addChampion($(this).data('transferOut'));
		return false;
	});
	
	DestinyTMI.champSelector.on('click', '.champion', function(){
		var el = $(this), champion = el.data('champion');
		if(el.hasClass('disabled')){
			return false;
		};
		if(el.hasClass('champion-unlocked')){
			if(!DestinyTMI.enabled || DestinyTMI.lockedState) return;
			DestinyTMI.addChampion(champion);
			return false;
		};
		if(el.hasClass('champion-free')){
			if(!DestinyTMI.enabled || DestinyTMI.lockedState) return;
			DestinyTMI.addFreeChampion(champion);
			return false;
		};
		if(el.hasClass('champion-locked')){
			if(!DestinyTMI.enabled || DestinyTMI.lockedState) return;
			DestinyTMI.beginChampionPurchase(champion);
			return false;
		};
		return false;
	});
	
	DestinyTMI.toolsUI
	.on('click', '.btn-close', function(){
		if(DestinyTMI.lockedState) return;
		DestinyTMI.cancelPick();
		return false;
	})
	.on('click', '.btn-confirm', function(){
		DestinyTMI.confirmPick();
		return false;
	});
	DestinyTMI.overlay.on('click', function(){
		if(DestinyTMI.lockedState) return;
		DestinyTMI.hide();
	});
	
})();