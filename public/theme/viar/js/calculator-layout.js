(function () {
    'use strict';
    function init() {
        var root = document.querySelector('#generate.calculator-v2');
        if (!root || root.dataset.layoutReady) return;
        root.dataset.layoutReady = '1';
        // Reference uploads are not part of the new canvas order flow.
        var referenceUpload=root.querySelector('input[name="photo_ex"]');
        if(referenceUpload)referenceUpload.closest('.kviz-input').remove();
        function layoutScale(){return parseFloat(getComputedStyle(root).zoom)||1;}
        var labels = document.getElementById('calculator-labels').dataset;
 var ui=JSON.parse(document.getElementById('calculator-ui-text').textContent);
        var ru=(document.documentElement.lang||'ru').startsWith('ru');
        var intro=root.previousElementSibling;
        if(intro&&intro.classList.contains('calculator-order-intro'))root.before(intro);
        var filter = root.querySelector('.filter-picture .filter-accordion');
        filter.classList.remove('filter-accordion');
        filter.classList.add('calculator-panels');
        // The existing mobile template may place the shape selector in a separate slot.
        // Bring the same nodes back before assigning steps; do not clone controls.
        var shapes=root.querySelector('.form-types');
        var shapeContent=shapes && shapes.closest('.accordion-content');
        if(shapeContent && shapeContent.parentElement!==filter){
            var shapeTitle=shapeContent.previousElementSibling;
            var sizesTitle=filter.querySelector('#generator_sizes2');
            if(shapeTitle)filter.insertBefore(shapeTitle,sizesTitle);
            filter.insertBefore(shapeContent,sizesTitle);
        }
        var titles = Array.from(filter.querySelectorAll(':scope > .accordion-title'));
        var groups = [0, 1, 1, -1, 3, 3, 3];
        titles.forEach(function (title, i) {
            title.dataset.calculatorPanel = groups[i];
            {var text=title.querySelector('span');if(text)text.textContent=text.textContent.replace(/^\s*\d+\.\s*\*?\s*/, '');}
            if (title.nextElementSibling) title.nextElementSibling.dataset.calculatorPanel = groups[i];
        });
        // Choose the canvas material before optional finishing; retain the same controls.
        var materialPanel=filter.querySelector('input[name="canvas_type"]').closest('.accordion-content');
        var finishPanel=filter.querySelector('input[name="decoration"]').closest('.accordion-content');
        var materialHeading=materialPanel.previousElementSibling,finishHeading=finishPanel.previousElementSibling;
        filter.insertBefore(materialHeading,finishHeading);
        filter.insertBefore(materialPanel,finishHeading);
        filter.prepend(document.getElementById('calculator-navigation').content.cloneNode(true));
        var extras = root.querySelector('.formalization-bottom--check');
        extras.dataset.calculatorPanel = '3';
        var packaging=extras.querySelector('input[name="boxes[]"]').closest('.kviz-wrap');
        packaging.dataset.calculatorPanel='3';
        packaging.classList.add('calculator-packaging');
        var regularPackaging=packaging.querySelector('input[name="boxes[]"][value="0"]');
        var regularPackagingCard=regularPackaging && regularPackaging.closest('.kviz-radio');
        if(regularPackagingCard)regularPackagingCard.parentElement.prepend(regularPackagingCard);
        packaging.addEventListener('click',function(e){
            if(e.target.closest('.calculator-addon-visual,.pic-pop,[role="button"]'))return;
            var card=e.target.closest('.kviz-radio');if(!card)return;
            var input=card.querySelector('input[name="boxes[]"]');
            var standard=packaging.querySelector('input[name="boxes[]"][value="0"]');
            if(!input||!standard||input===standard||!card.classList.contains('kviz-radio_active'))return;
            e.preventDefault();e.stopImmediatePropagation();
            standard.closest('.kviz-radio').click();
            // Apply after the cancelled label click restores the native radio state.
            setTimeout(function(){standard.checked=true;standard.dispatchEvent(new Event('change',{bubbles:true}));},0);
        },true);

        filter.appendChild(packaging);
        filter.appendChild(extras);
        filter.appendChild(document.getElementById('calculator-navigation-bottom').content.cloneNode(true));
        var heading = root.querySelector('.page-c-title');
        heading.textContent = labels.title;
        var subtitle = document.createElement('p'); subtitle.className = 'calculator-subtitle'; subtitle.textContent = labels.subtitle;
        heading.after(subtitle);
        root.querySelectorAll('.calcForm').forEach(function (shape) {
            var label = labels['shape-' + shape.dataset.id];
            var caption = document.createElement('span'); caption.className = 'calculator-shape-name'; caption.textContent = label;
            shape.appendChild(caption); shape.setAttribute('role', 'button'); shape.tabIndex = 0; shape.setAttribute('aria-label', label);
            shape.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); shape.click(); } });
        });
        var hint = document.createElement('p'); hint.className = 'calculator-size-hint'; hint.dataset.calculatorPanel = '1'; hint.textContent = labels.hint;
        filter.insertBefore(hint, filter.querySelector('.calculator-navigation-bottom'));
        var summary = document.createElement('div'); summary.className = 'calculator-summary'; summary.setAttribute('aria-live', 'polite');summary.hidden=true;summary.style.display='none';
        root.querySelector('.formalizaton__submit').prepend(summary);
        var rawTotal=root.querySelector('.totalPriceNew');
        var priceBox=rawTotal.closest('.formalization-price');
        Array.from(priceBox.childNodes).forEach(function(n){if(n.nodeType===3)n.textContent='';});
        priceBox.prepend(document.createTextNode(labels.total+' '));
        var money=new Intl.NumberFormat(document.documentElement.lang||'ru',{style:'currency',currency:'EUR',minimumFractionDigits:2});
        // Present real catalog packaging images without duplicating order inputs.
        root.querySelectorAll('.calculator-packaging .kviz-radio,.delivery-inputs .kviz-radio,.kviz-radio:has(input[name="decoration"]),.kviz-radio:has(input[name="canvas_type"]),.kviz-radio:has(input[name="boxes2[]"])').forEach(function(card){
            card.classList.add('calculator-addon-card');
            if(card.querySelector('input[name="decoration"],input[name="canvas_type"],input[name="boxes2[]"]'))card.classList.add('calculator-material-card');
            var input=card.querySelector('input[type="radio"]'),picture=card.querySelector('.pic-pop picture');
            var visual=document.createElement('div');visual.className='calculator-addon-visual';visual.setAttribute('aria-hidden','true');
            if(picture){var copy=picture.cloneNode(true);copy.querySelectorAll('img').forEach(function(img){img.alt='';});visual.appendChild(copy);}
            else visual.innerHTML='<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="32" cy="36" r="22"/><path d="M25 6h14M32 6v8M48 17l5-5M32 23v14l-9 8"/><path d="m23 36 6 6 13-14"/></svg>';
            card.prepend(visual);
            if(input&&input.name==='boxes[]'){
                var price=document.createElement('strong');price.className='calculator-addon-price';
                price.textContent=(Number(input.value)>0?'+':'')+money.format(Number(input.value));card.appendChild(price);
            }
        });
        var totalDisplay=document.createElement('span');totalDisplay.className='calculator-total-value';
        rawTotal.parentElement.after(totalDisplay);rawTotal.parentElement.style.display='none';
        root.querySelectorAll('.size-card-prices b,.size-card-prices del').forEach(function(el){var n=parseFloat(el.textContent.replace(',','.'));if(Number.isFinite(n))el.textContent=money.format(n);});
        var priceLines=document.createElement('div');priceLines.className='calculator-price-lines';
        var priceDialog=document.createElement('dialog');priceDialog.className='calculator-price-dialog';
        var priceTitle=document.createElement('h2');priceTitle.id='calculator-price-title';priceTitle.textContent=ui.price_breakdown;
        priceDialog.setAttribute('aria-labelledby',priceTitle.id);
        var priceClose=document.createElement('button');priceClose.type='button';priceClose.className='jcf-ignore';priceClose.textContent=ui.close+' ×';
        var detailTotal=document.createElement('div');detailTotal.className='calculator-detail-total';
        priceDialog.append(priceClose,priceTitle,priceLines,detailTotal);root.appendChild(priceDialog);
        priceClose.addEventListener('click',function(){priceDialog.close();});
        priceDialog.addEventListener('click',function(e){if(e.target===priceDialog){var r=priceDialog.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)priceDialog.close();}});
        function priceTrigger(){var b=document.createElement('button');b.type='button';b.className='calculator-price-trigger jcf-ignore';b.textContent=priceTitle.textContent;b.setAttribute('aria-haspopup','dialog');b.addEventListener('click',function(){priceDialog.showModal();});return b;}
        priceBox.appendChild(priceTrigger());
        root.addEventListener('calculator:price',updateSummary);
        function updateSummary() {
            var amount=parseFloat(rawTotal.textContent.replace(',','.'));if(Number.isFinite(amount)){var formatted=money.format(amount);if(totalDisplay.textContent!==formatted){totalDisplay.textContent=formatted;totalDisplay.animate([{background:'#ffe1d3'},{background:'transparent'}],{duration:650});}if(stickyPrice)stickyPrice.textContent=formatted;}
            detailTotal.textContent=labels.total+' '+totalDisplay.textContent;
            if(frameConfirmation)syncFrameConfirmation();
            var chosen = root.querySelector('.calcSize.kviz-radio_active');
            var material = root.querySelector('input[name="canvas_type"]:checked');
            var edge = root.querySelector('input[name="canvas_edge_mode"]:checked');
            var parts = [];
            if (chosen) parts.push(chosen.dataset.size.replace('x', ' × ') + ' cm');
            if (material) parts.push(material.closest('label').querySelector('span').textContent.trim());
            if (edge) parts.push(edge.closest('label').querySelector('.edge-caption').textContent.trim());
            var breakdown=[];try{breakdown=JSON.parse(root.dataset.priceBreakdown||'[]');}catch(e){}
            breakdown.forEach(function(item){if(item.key!=='base'&&item.key!=='material'&&item.name&&(item.amount!==0||item.key==='improvement'))parts.push(item.key==='frame'?labels.frame+': '+item.name:item.name);});
            var sizeCaption=chosen&&chosen.dataset.size?chosen.dataset.size.replace('x',' × ')+' cm':'';
            var selectedCosts={material:'canvas_type',decoration:'decoration',improvement:'boxes2[]'};
            root.querySelectorAll('.calculator-selected-cost').forEach(function(el){el.remove();});
            breakdown.forEach(function(item){if(!selectedCosts[item.key]||!item.amount)return;var input=root.querySelector('input[name="'+selectedCosts[item.key]+'"]:checked');var card=input&&input.closest('.kviz-radio');if(card){var cost=document.createElement('strong');cost.className='calculator-selected-cost';cost.textContent=(item.amount>0?'+':'')+money.format(item.amount);card.appendChild(cost);}});
            var signature=JSON.stringify([breakdown,sizeCaption]);
            if(priceLines.dataset.signature!==signature){
                priceLines.dataset.signature=signature;priceLines.replaceChildren();
                breakdown.filter(function(item){return item.key==='base'||item.amount!==0||item.key==='improvement'&&item.name;}).forEach(function(item){
                    var line=document.createElement('div'),name=document.createElement('span'),price=document.createElement('strong');
                    name.textContent=item.key==='base'?labels.basePrice+(sizeCaption?' · '+sizeCaption:''):item.key==='frame'?labels.frame+': '+item.name:item.name;
                    price.textContent=(item.key!=='base'&&item.amount>0?'+':'')+money.format(item.amount);
                    line.append(name,price);priceLines.appendChild(line);
                });
            }
            var text = parts.join(' · '); if (summary.textContent !== text) summary.textContent = text;
            root.querySelectorAll('.calcForm').forEach(function (shape) { shape.setAttribute('aria-pressed', shape.classList.contains('active') ? 'true' : 'false'); });
        }
        var uploadSummary=document.createElement('button');
        uploadSummary.type='button';uploadSummary.className='calculator-upload-summary jcf-ignore';uploadSummary.hidden=true;
        var thumbnail=document.createElement('img');thumbnail.alt='';
        var uploadCaption=document.createElement('span');uploadCaption.textContent=filter.querySelector('[data-calculator-step="0"]').textContent.replace(/^\s*1\s*/, '').trim();
        uploadSummary.append(thumbnail,uploadCaption);filter.querySelector('.calculator-steps').after(uploadSummary);
        uploadSummary.addEventListener('click',function(){openPhoto();});
        function syncUpload(){var img=root.querySelector('#imgs .is-load img');uploadSummary.hidden=!img||current===0;if(img&&thumbnail.src!==img.src)thumbnail.src=img.src;if(footerThumbnail){footerThumbnail.hidden=!img;if(img)footerThumbnail.src=img.src;}}
        new MutationObserver(syncUpload).observe(root.querySelector('#imgs'),{childList:true,subtree:true,attributes:true,attributeFilter:['src','class']});
        // Reuse the existing frame controls without duplicating their inputs or prices.
        root.querySelectorAll('.filter-interior .frames').forEach(function(el){el.dataset.calculatorPanel='2';if(el.classList.contains('accordion-title'))el.textContent=labels.frame;filter.insertBefore(el,filter.querySelector('.calculator-navigation-bottom'));});
        var frameRates=JSON.parse(document.getElementById('calculator-frame-rates').textContent);
        function frameAmount(card, meters) {
            var rate=frameRates[card.dataset.id];
            return rate!=null?Math.round(meters*Number(rate)*100)/100:Number(card.dataset.price)||0;
        }

        var frameGrid=root.querySelector('.ramu-slider');
        frameGrid.setAttribute('role','radiogroup');frameGrid.setAttribute('aria-label',labels.frame);
        var frameNote=document.createElement('p');frameNote.className='calculator-frame-note';frameNote.textContent=labels.frameOptional;frameGrid.before(frameNote);
        var noFrame=frameGrid.querySelector('[ramu_zero_item]');
        if(noFrame&&!noFrame.querySelector('.img')){
            var bareCanvas=document.createElement('div');bareCanvas.className='img calculator-no-frame';
            bareCanvas.innerHTML='<svg viewBox="0 0 180 110" aria-hidden="true"><ellipse cx="92" cy="99" rx="53" ry="5" fill="#493f32" opacity=".13"/><path d="M36 17 47 10 47 90 36 97Z" fill="#b7a58b"/><path d="M47 10 143 19 143 98 47 90Z" fill="#b8d9dd"/><path d="M47 54 79 32 107 66 125 48 143 69 143 98 47 90Z" fill="#6e9290"/><path d="M47 74 78 62 112 82 143 72 143 98 47 90Z" fill="#d3a76b"/><path d="M47 86 88 74 119 94 143 85 143 98 47 90Z" fill="#a96947"/><ellipse cx="119" cy="38" rx="10" ry="10" fill="#fff0c5"/><path d="M36 67 47 54 47 90 36 97Z" fill="#96704d"/><path d="M36 17 47 10 143 19 134 24Z" fill="#d3e6e6"/></svg>';
            noFrame.prepend(bareCanvas);
        }
        var frameZoom=document.createElement('dialog');frameZoom.className='calculator-frame-zoom-dialog';
        var frameZoomClose=document.createElement('button');frameZoomClose.type='button';frameZoomClose.textContent=ui.close+' ×';
        var frameZoomTitle=document.createElement('h2');frameZoomTitle.id='frame-zoom-title';frameZoom.setAttribute('aria-labelledby','frame-zoom-title');
        var frameZoomImage=document.createElement('img');
        var frameZoomBody=document.createElement('div');frameZoomBody.className='calculator-frame-detail-layout';
        var frameZoomInfo=document.createElement('section');frameZoomInfo.className='calculator-frame-detail-info';
        frameZoomInfo.appendChild(frameZoomTitle);frameZoomBody.append(frameZoomImage,frameZoomInfo);frameZoom.append(frameZoomClose,frameZoomBody);root.appendChild(frameZoom);
        function fillFrameDetails(card){
            var text=ui.frame_details, name=card.querySelector('.code').textContent.trim(), data={};
            try{data=JSON.parse(card.dataset.frameDetails||'{}');}catch(e){}
            frameZoomTitle.textContent=text.title;
            frameZoomInfo.replaceChildren(frameZoomTitle);
            var article=document.createElement('p');article.className='calculator-frame-detail-article';article.textContent=text.article+': '+name;frameZoomInfo.appendChild(article);
            var price=document.createElement('strong');price.className='calculator-frame-detail-price';price.textContent=(frameRates[card.dataset.id]!=null?money.format(Number(frameRates[card.dataset.id]))+' / '+ui.linear_meter:money.format(Number(card.dataset.price)||0));frameZoomInfo.appendChild(price);
            var list=document.createElement('dl');
            ['style','material','width','height','rebate','profile','shade'].forEach(function(key){
                var value=key==='material'&&card.dataset.profileSample==='true'?ui.plastic_profile:data[key];
                if(value==null||value==='')return;
                var term=document.createElement('dt'),definition=document.createElement('dd');term.textContent=text[key]+':';
                definition.textContent=['width','height','rebate'].includes(key)?Number(value).toLocaleString(document.documentElement.lang||'ru')+' '+text.cm:(text.values[value]||value);
                list.append(term,definition);
            });frameZoomInfo.appendChild(list);
        }
        frameZoomClose.addEventListener('click',function(){frameZoom.close();});
        frameZoom.addEventListener('click',function(e){if(e.target===frameZoom){var r=frameZoom.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)frameZoom.close();}});
        function syncFrames(){frameGrid.querySelectorAll('.ramu-item').forEach(function(card){var active=card.classList.contains('active')&&!root.classList.contains('frame-demo-active');card.setAttribute('aria-checked',String(active));card.tabIndex=active?0:-1;});if(frameConfirmation)syncFrameConfirmation();}
        function prepareFrameCard(card){
            if(frameRates[card.dataset.id]!=null&&!card.hasAttribute('ramu_zero_item'))card.querySelector('.price').textContent=money.format(Number(frameRates[card.dataset.id]))+' / '+ui.linear_meter;
            card.setAttribute('role','radio');
            card.setAttribute('aria-label',card.querySelector('.code').textContent.trim()+', '+card.querySelector('.price').textContent.trim());
            var input=card.querySelector('input');input.tabIndex=-1;
            var img=card.querySelector('img');if(img){img.alt=card.querySelector('.code').textContent.trim();img.removeAttribute('title');}
            var article=document.createElement('small');article.className='calculator-frame-article';article.textContent=card.dataset.article||'';article.hidden=!article.textContent;card.querySelector('.code').after(article);
            if(img){var zoom=document.createElement('button');zoom.type='button';zoom.className='calculator-frame-zoom';zoom.setAttribute('aria-label',(ui.enlarge_sample+': ')+img.alt);zoom.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M7 10h6m-3-3v6"/></svg>';card.querySelector('.img').appendChild(zoom);}
            card.addEventListener('keydown',function(e){
                if(e.target.closest('.calculator-frame-zoom'))return;
                var cards=Array.from(frameGrid.querySelectorAll('.ramu-item')).filter(function(c){return !c.closest('label').hidden;}),index=cards.indexOf(card),next=index;
                if(e.key==='ArrowRight'||e.key==='ArrowDown')next=(index+1)%cards.length;
                else if(e.key==='ArrowLeft'||e.key==='ArrowUp')next=(index+cards.length-1)%cards.length;
                else if(e.key!=='Enter'&&e.key!==' ')return;
                e.preventDefault();cards[next].click();cards[next].focus({preventScroll:true});syncFrames();
            });
        }
        frameGrid.querySelectorAll('.ramu-item').forEach(prepareFrameCard);
        var catalogueTemplate=root.querySelector('#calculator-frame-catalogue');
        function remainingFrames(){return catalogueTemplate?catalogueTemplate.content.children.length:0;}
        function mountFrames(count){
            var fragment=document.createDocumentFragment();
            while(count-->0&&remainingFrames()){
                var label=catalogueTemplate.content.firstElementChild,card=label.querySelector('.ramu-item');
                label.hidden=true;card.dataset.deferredFrame='true';prepareFrameCard(card);fragment.appendChild(label);
            }
            frameGrid.appendChild(fragment);
        }
        var galleryText=ui.frame_gallery;
        var galleryOpen=document.createElement('button');galleryOpen.type='button';galleryOpen.className='calculator-frame-gallery-open';galleryOpen.textContent=galleryText.title+' →';frameNote.after(galleryOpen);
        var gallery=document.createElement('dialog');gallery.className='calculator-frame-gallery';gallery.setAttribute('aria-labelledby','frame-gallery-title');
        var galleryHeader=document.createElement('header'),galleryTitle=document.createElement('h2'),galleryClose=document.createElement('button');
        galleryTitle.id='frame-gallery-title';galleryTitle.textContent=galleryText.title;galleryClose.type='button';galleryClose.textContent=ui.close+' ×';galleryHeader.append(galleryTitle,galleryClose);
        var galleryList=document.createElement('div'),galleryMore=document.createElement('button');galleryMore.type='button';galleryMore.className='calculator-frame-gallery-more';galleryMore.textContent=galleryText.more;gallery.append(galleryHeader,galleryList,galleryMore);root.appendChild(gallery);
        var galleryCards=[],galleryCount=0;
        function addGalleryOptions(){
            if(galleryCount+4>galleryCards.length&&remainingFrames()){mountFrames(4);filterFrameCatalogue();galleryCards=Array.from(frameGrid.querySelectorAll('.ramu-item'));}
            var size=root.querySelector('.calcSize.kviz-radio_active'),dimensions=size?size.dataset.size.split('x').map(Number):[50,70],meters=2*(dimensions[0]+dimensions[1])/100;
            var selected=frameGrid.querySelector('.ramu-item.active')||noFrame,total=parseFloat(rawTotal.textContent.replace(',','.'))||0;
            var base=total-frameAmount(selected,meters);
            galleryCards.slice(galleryCount,galleryCount+4).forEach(function(card){
                var row=document.createElement('article');row.className='calculator-frame-gallery-row';
                var preview=document.createElement('canvas');preview.className='calculator-frame-gallery-preview';preview.setAttribute('role','img');preview.setAttribute('aria-label',card.querySelector('.code').textContent.trim());
                var info=document.createElement('div'),title=document.createElement('h3');title.textContent=card.querySelector('.code').textContent.trim();info.appendChild(title);
                var data={};try{data=JSON.parse(card.dataset.frameDetails||'{}');}catch(e){}
                var fields=document.createElement('dl');
                [['material',card.dataset.profileSample==='true'?ui.plastic_profile:null],['width',card.dataset.frameWidthMm?Number(card.dataset.frameWidthMm)/10+' '+ui.frame_details.cm:null],['height',card.dataset.frameDepthMm?Number(card.dataset.frameDepthMm)/10+' '+ui.frame_details.cm:null],['shade',data.shade?(ui.frame_details.values[data.shade]||data.shade):null]].forEach(function(pair){if(!pair[1])return;var dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=ui.frame_details[pair[0]];dd.textContent=pair[1];fields.append(dt,dd);});info.appendChild(fields);
                var price=document.createElement('div');price.className='calculator-frame-gallery-price';var rate=Number(frameRates[card.dataset.id]||0),frameCost=frameAmount(card,meters);
                var sizeText=document.createElement('p');sizeText.textContent=dimensions.join(' × ')+' '+ui.frame_details.cm;
                var fullPrice=document.createElement('strong');fullPrice.textContent=labels.total+' '+money.format(Math.round((base+frameCost)*100)/100);
                var cost=document.createElement('p');cost.textContent=galleryText.frame_cost+': '+money.format(frameCost)+(rate?' · '+money.format(rate)+' / '+ui.linear_meter:'');
                var choose=document.createElement('button');choose.type='button';choose.textContent=galleryText.choose;choose.addEventListener('click',function(){if(root.classList.contains('frame-demo-active'))setDemo(null);if(!card.classList.contains('active'))card.click();gallery.close();frameConfirmButton.focus({preventScroll:true});});
                price.append(sizeText,fullPrice,cost,choose);row.append(preview,info,price);galleryList.appendChild(row);
                root.dispatchEvent(new CustomEvent('frame-gallery-render',{detail:{canvas:preview,card:card}}));
            });galleryCount=Math.min(galleryCount+4,galleryCards.length);galleryMore.hidden=galleryCount>=galleryCards.length&&!remainingFrames();
        }
        galleryOpen.addEventListener('click',function(){galleryList.replaceChildren();galleryCards=Array.from(frameGrid.querySelectorAll('.ramu-item'));galleryCount=0;gallery.showModal();addGalleryOptions();});
        galleryClose.addEventListener('click',function(){gallery.close();});galleryMore.addEventListener('click',addGalleryOptions);
        // A complete catalogue needs direct article lookup without loading every sample image.
        var catalogueLimit=24,catalogueMore=document.createElement('button');catalogueMore.type='button';catalogueMore.className='calculator-frame-gallery-open';catalogueMore.textContent=galleryText.more;frameGrid.after(catalogueMore);
        function filterFrameCatalogue(){var query='',matched=0;frameGrid.querySelectorAll('.ramu-item').forEach(function(card){var label=card.closest('label'),match=!query||card.hasAttribute('ramu_zero_item')||card.querySelector('.code').textContent.toLocaleLowerCase().includes(query);if(match)matched++;if(label)label.hidden=!match||matched>catalogueLimit;});frameGrid.querySelectorAll('.calculator-demo-frame').forEach(function(card){card.hidden=!!query;});catalogueMore.hidden=matched<=catalogueLimit&&!remainingFrames();}
        catalogueMore.addEventListener('click',function(){
            mountFrames(24);
            var hiddenCards=Array.from(frameGrid.querySelectorAll('.ramu-item')).filter(function(card){return card.closest('label').hidden;});
            catalogueLimit+=24;filterFrameCatalogue();
            var firstNew=hiddenCards.find(function(card){return !card.closest('label').hidden;});
            if(!firstNew)return;
            // Keep the existing demo examples above the newly revealed catalogue rows.
            var firstLabel=firstNew.closest('label');
            frameGrid.querySelectorAll('.calculator-demo-frame').forEach(function(card){
                if(firstLabel.compareDocumentPosition(card)&Node.DOCUMENT_POSITION_FOLLOWING)frameGrid.insertBefore(card,firstLabel);
            });
            requestAnimationFrame(function(){
                firstNew.focus({preventScroll:true});
                var panel=frameGrid.closest('.frames');
                if(panel&&panel.scrollHeight>panel.clientHeight&&/auto|scroll/.test(getComputedStyle(panel).overflowY)){
                    var cardRect=firstNew.getBoundingClientRect(),panelRect=panel.getBoundingClientRect();
                    panel.scrollTo({top:panel.scrollTop+(cardRect.top-panelRect.top)/layoutScale()-panel.clientTop,behavior:'smooth'});
                }
            });
        });filterFrameCatalogue();
        var demoNotice=document.createElement('p');demoNotice.className='calculator-frame-demo-notice';demoNotice.hidden=true;demoNotice.textContent=ui.demo_notice;frameGrid.after(demoNotice);
        var confirmedFrameId=(frameGrid.querySelector('.ramu-item.active')||noFrame).dataset.id;
        var frameConfirmation=document.createElement('div');frameConfirmation.className='calculator-frame-confirmation';
        var frameSelectionText=document.createElement('p');frameSelectionText.setAttribute('aria-live','polite');
        var frameConfirmButton=document.createElement('button');frameConfirmButton.type='button';
        frameConfirmation.append(frameSelectionText,frameConfirmButton);
        var frameOrderBadge=document.createElement('button');frameOrderBadge.type='button';frameOrderBadge.className='calculator-frame-order-badge';frameOrderBadge.hidden=true;
        var frameOrderIcon=document.createElement('span');frameOrderIcon.setAttribute('aria-hidden','true');frameOrderIcon.textContent='▣ ✓';
        var frameOrderLabel=document.createElement('span');frameOrderLabel.textContent=labels.frameShort+' · '+ui.confirmed;
        frameOrderBadge.append(frameOrderIcon,frameOrderLabel);root.querySelector('#PhotoEditor').appendChild(frameOrderBadge);
        frameOrderBadge.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();if(frameOpen)frameOpen.click();});
        function syncFrameConfirmation(){
            var demo=frameGrid.querySelector('.calculator-demo-frame.is-preview'),selected=demo||frameGrid.querySelector('.ramu-item.active')||noFrame;
            var name=selected.querySelector('.code').textContent.trim(),price=selected.querySelector('.price');
            var size=root.querySelector('.calcSize.kviz-radio_active');
            var dimensions=size?size.dataset.size.split('x').map(Number):[0,0];
            var meters=2*(dimensions[0]+dimensions[1])/100,rate=Number(frameRates[selected.dataset.id]||0);
            var signature=selected.dataset.id+':'+(size?size.dataset.size:'')+':'+rate;
            if(frameConfirmation.dataset.selection&&frameConfirmation.dataset.selection!==signature)confirmedFrameId='';
            var confirmed=!demo&&(selected===noFrame||confirmedFrameId===signature);
            root.dataset.confirmedFrame=confirmed&&selected!==noFrame?signature:'';
            frameOrderBadge.hidden=!root.dataset.confirmedFrame;
            frameOrderBadge.title=labels.frameShort+' · '+ui.confirmed+': '+name;
            frameOrderBadge.setAttribute('aria-label',frameOrderBadge.title);
            frameSelectionText.textContent=demo?ui.preview_only+': '+name:ui.frame_size_price+' '+dimensions.join(' × ')+' cm · '+name+' — '+money.format(frameAmount(selected,meters));
            frameConfirmation.dataset.selection=signature;
            frameConfirmButton.disabled=!!demo||confirmed;
            frameConfirmButton.textContent=demo?(ui.demo_unavailable):confirmed?('✓ '+ui.confirmed):(ui.confirm_order);
            root.classList.toggle('frame-selection-unconfirmed',!confirmed);
        }
        frameConfirmButton.addEventListener('click',function(){var selected=frameGrid.querySelector('.ramu-item.active');if(!selected||root.classList.contains('frame-demo-active'))return;confirmedFrameId=frameConfirmation.dataset.selection;syncFrameConfirmation();});
        // An unconfirmed frame is only a fitting preview until the customer returns to Canvas.
        root.addEventListener('canvas-view-open',function(){
            syncFrameConfirmation();
            if(!root.classList.contains('frame-selection-unconfirmed'))return;
            setDemo(null);
            if(!noFrame.classList.contains('active'))noFrame.click();
            syncFrames();
        },true);
        root.addEventListener('click',function(e){if(root.classList.contains('frame-selection-unconfirmed')&&e.target.closest('#t3_submit_btn')){e.preventDefault();e.stopImmediatePropagation();if(frameOpen)frameOpen.click();frameConfirmation.scrollIntoView({block:'center'});frameConfirmButton.focus({preventScroll:true});}},true);
        function setDemo(card){root.querySelectorAll('.calculator-demo-frame').forEach(function(c){c.classList.toggle('is-preview',c===card);c.setAttribute('aria-pressed',String(c===card));});root.classList.toggle('frame-demo-active',!!card);demoNotice.hidden=!card;syncFrames();root.dispatchEvent(new Event('frame-preview-update'));window.dispatchEvent(new Event('resize'));}
        root.addEventListener('click',function(e){if(root.classList.contains('frame-demo-active')&&e.target.closest('#t3_submit_btn,[data-calculator-next],.calculator-sticky-next')){e.preventDefault();e.stopImmediatePropagation();if(frameOpen)frameOpen.click();frameConfirmation.scrollIntoView({block:'center'});}},true);
        frameGrid.addEventListener('click',function(e){
            var card=e.target.closest('.ramu-item');if(!card)return;
            if(e.target.closest('.calculator-frame-zoom')){e.preventDefault();e.stopImmediatePropagation();var image=card.querySelector('.img img');frameZoomImage.src=card.dataset.zoomSrc||image.currentSrc||image.src;frameZoomImage.alt=image.alt;fillFrameDetails(card);frameZoom.showModal();return;}
            setDemo(null);
            // Stop the wrapping label from sending a second click to its radio.
            e.preventDefault();
            if(card.classList.contains('active')&&!card.hasAttribute('ramu_zero_item')){
                e.stopImmediatePropagation();frameGrid.querySelector('[ramu_zero_item]').click();return;
            }
            if(card.dataset.deferredFrame){frameGrid.querySelectorAll('.ramu-item.active').forEach(function(c){c.classList.remove('active');});card.classList.add('active');}
            var input=card.querySelector('input');if(input)input.checked=true;
        },true);
        frameGrid.addEventListener('click',function(){setTimeout(function(){syncFrames();root.dispatchEvent(new Event('frame-preview-update'));},0);});syncFrames();
        var scroller=document.createElement('div');scroller.className='calculator-step-scroll';
        Array.from(filter.children).forEach(function(el){if(el.hasAttribute('data-calculator-panel'))scroller.appendChild(el);});
        filter.insertBefore(scroller,filter.querySelector('.calculator-navigation-bottom'));
        var actions=filter.querySelector('.calculator-navigation-bottom');
        root.querySelector('.formalizaton__submit').appendChild(actions);
        var footerThumbnail=document.createElement('img');footerThumbnail.className='calculator-footer-photo';footerThumbnail.alt='';footerThumbnail.hidden=true;
        summary.before(footerThumbnail);
        var replaceLabel=document.createElement('span');replaceLabel.className='calculator-replace';replaceLabel.textContent=labels.replace;uploadSummary.appendChild(replaceLabel);
        var previewToggle=document.createElement('button');previewToggle.type='button';previewToggle.className='calculator-preview-toggle jcf-ignore';previewToggle.textContent=labels.hidePreview;previewToggle.setAttribute('aria-label',labels.hidePreview);previewToggle.title=labels.hidePreview;previewToggle.setAttribute('aria-expanded','true');
        previewToggle.hidden=true;previewToggle.style.display='none';
        var pinPreview=document.createElement('button');pinPreview.type='button';pinPreview.className='calculator-pin-preview jcf-ignore';pinPreview.setAttribute('aria-pressed','false');
        pinPreview.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-3 1-4 5v3l-7-7h3l5-4 1-3Z"/><path d="m10 14-7 7"/></svg>';
        var pinPreviewTop=pinPreview.cloneNode(true);pinPreviewTop.classList.add('calculator-pin-preview-top');
        pinPreviewTop.addEventListener('click',function(){pinPreview.click();});
        var pinSpacer=document.createElement('div');pinSpacer.hidden=true;root.before(pinSpacer);
        function syncPin(){var pinned=root.classList.contains('preview-pinned');pinPreview.setAttribute('aria-label',pinned?labels.unpinPreview:labels.pinPreview);pinPreview.title=pinned?labels.unpinPreview:labels.pinPreview;pinPreview.setAttribute('aria-pressed',String(pinned));['aria-label','title','aria-pressed'].forEach(function(name){pinPreviewTop.setAttribute(name,pinPreview.getAttribute(name));});}
        root.querySelector('.slider-tabs').append(pinPreview,pinPreviewTop);syncPin();
        document.addEventListener('canvas-cart-added',function(){
            root.classList.remove('preview-pinned','desktop-pinned');
            pinSpacer.hidden=true;
            syncPin();measure();
        });
        pinPreview.addEventListener('click',function(){
            // Remember the normal preview before fixed layout changes its available height.
            if(window.innerWidth>=1000&&!root.classList.contains('preview-pinned')){
                root.style.setProperty('--standard-preview-height',editorHost.getBoundingClientRect().height/layoutScale()+'px');
            }
            root.classList.toggle('preview-pinned');
            if(root.classList.contains('preview-pinned')&&root.classList.contains('preview-collapsed'))previewToggle.click();
            if(window.innerWidth>=1000&&root.classList.contains('preview-pinned')&&root.classList.contains('sizes-expanded'))allSizes.click();
            syncPin();measure();
        });

        previewToggle.addEventListener('click',function(){var collapsed=root.classList.toggle('preview-collapsed');previewToggle.setAttribute('aria-expanded',String(!collapsed));previewToggle.textContent=collapsed?labels.showPreview:labels.hidePreview;previewToggle.setAttribute('aria-label',previewToggle.textContent);previewToggle.title=previewToggle.textContent;requestAnimationFrame(function(){root.scrollIntoView({block:'start'});});});
        var allSizes=document.createElement('button');allSizes.type='button';allSizes.className='calculator-all-sizes jcf-ignore';allSizes.textContent=labels.allSizes+' ↓';allSizes.setAttribute('aria-expanded','false');hint.before(allSizes);
        allSizes.dataset.calculatorPanel='1';
        allSizes.addEventListener('click',function(){var expanded=root.classList.toggle('sizes-expanded');allSizes.setAttribute('aria-expanded',String(expanded));allSizes.textContent=(expanded?labels.lessSizes:labels.allSizes)+(expanded?' ↑':' ↓');measure();fitSizeRows();
            if(!expanded)requestAnimationFrame(function(){allSizes.scrollIntoView({block:'nearest'});});
        });
        root.querySelector('.slider-tabs a[data-tabs="tabs-item1"]').textContent=labels.canvasShort;
        root.querySelector('.slider-tabs a[data-tabs="tabs-item2"]').textContent=labels.roomShort;
        // Finish options have their own details modal; opening never selects a service.
        var standardFinish=root.querySelector('input[name="decoration"][data-id="5"]');
        if(standardFinish){var standardCard=standardFinish.closest('.kviz-radio');standardCard.parentElement.prepend(standardCard);}
        var interiorCanvas=root.querySelector('input[name="canvas_type"][data-id="2"]');
        if(interiorCanvas){var interiorCard=interiorCanvas.closest('.kviz-radio');interiorCard.parentElement.prepend(interiorCard);}
        var materialText=JSON.parse(document.getElementById('calculator-material-text').textContent);
        var finishText=JSON.parse(document.getElementById('calculator-finish-text').textContent);

        root.querySelectorAll('input[name="decoration"],input[name="canvas_type"]').forEach(function(input){
            var detailText=input.name==='canvas_type'?Object.assign({},finishText,materialText):finishText;
            var card=input.closest('.kviz-radio'),pop=card.querySelector('.pic-pop'),icon=card.querySelector('label>span>img'),thumbnail=card.querySelector('.calculator-addon-visual');
            var details=document.createElement('button');details.type='button';details.className='calculator-preparation-details calculator-finish-details jcf-ignore';details.textContent=detailText.details;details.setAttribute('aria-expanded','false');card.appendChild(details);
            var finishDialog=document.createElement('div');finishDialog.className='calculator-option-details';finishDialog.hidden=true;card.appendChild(finishDialog);
            function openDetails(e){
                e.preventDefault();e.stopImmediatePropagation();
                if(!finishDialog.hidden){finishDialog.hidden=true;details.setAttribute('aria-expanded','false');details.textContent=detailText.details;return;}
                finishDialog.replaceChildren();
                var close=document.createElement('button');close.type='button';close.className='calculator-preparation-close';close.textContent=detailText.close+' ×';close.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();finishDialog.hidden=true;details.setAttribute('aria-expanded','false');details.textContent=detailText.details;});
                var title=document.createElement('h2');title.textContent=input.dataset.name.trim();finishDialog.append(close,title);
                function paragraph(heading,text){if(!text)return;var h=document.createElement('h3');h.textContent=heading;var p=document.createElement('p');p.textContent=text;finishDialog.append(h,p);}
                paragraph(detailText.description,input.name==='canvas_type'?detailText.descriptions[input.dataset.id]:(pop&&pop.querySelector('.hint')?pop.querySelector('.hint').textContent.trim():''));
                paragraph(detailText.advice,detailText.services[input.dataset.id]);
                paragraph(detailText.important,detailText.limit);
                var picture=pop&&pop.querySelector('picture');if(picture){paragraph(detailText.example,detailText.example_note);finishDialog.appendChild(picture.cloneNode(true));}
                var collapseBottom=document.createElement('button');collapseBottom.type='button';collapseBottom.className='calculator-option-collapse jcf-ignore';collapseBottom.textContent=((document.documentElement.lang||'ru').indexOf('ru')===0?'Свернуть':(document.documentElement.lang||'').indexOf('lv')===0?'Sakļaut':'Collapse')+' ⌃';
                collapseBottom.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();finishDialog.hidden=true;details.setAttribute('aria-expanded','false');details.textContent=detailText.details;details.focus({preventScroll:true});details.scrollIntoView({block:'nearest'});});finishDialog.appendChild(collapseBottom);
                finishDialog.hidden=false;details.setAttribute('aria-expanded','true');details.textContent=detailText.close+' ↑';
            }
            details.addEventListener('click',openDetails);
            [icon,thumbnail].filter(Boolean).forEach(function(trigger){trigger.setAttribute('role','button');trigger.setAttribute('aria-label',detailText.details);trigger.setAttribute('aria-expanded','false');trigger.tabIndex=0;trigger.addEventListener('click',openDetails,true);trigger.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' ')openDetails(e);});});
        });
        // Make existing image/info explanations operable by keyboard and touch.
        root.querySelectorAll('.kviz-radio label>span>img').forEach(function(icon){if(icon.closest('.kviz-radio').querySelector('input[name="decoration"],input[name="canvas_type"]'))return;var pop=icon.closest('.kviz-radio').querySelector('.pic-pop');if(!pop)return;icon.tabIndex=0;icon.setAttribute('role','button');icon.setAttribute('aria-label',labels.details);icon.setAttribute('aria-expanded','false');
            function close(){pop.classList.remove('calculator-info-open');icon.setAttribute('aria-expanded','false');}
            function toggle(e){e.preventDefault();e.stopPropagation();var open=pop.classList.toggle('calculator-info-open');icon.setAttribute('aria-expanded',String(open));}
            // Capture image/magnifier clicks before legacy zoom or label selection handlers.
            pop.addEventListener('click',function(e){if(!pop.classList.contains('calculator-info-open'))return;e.preventDefault();e.stopImmediatePropagation();close();},true);
            var card=icon.closest('.kviz-radio'),choice=card&&card.querySelector('input');
            var thumbnail=card&&card.querySelector('.calculator-addon-visual');
            if(thumbnail){thumbnail.removeAttribute('aria-hidden');thumbnail.setAttribute('role','button');thumbnail.setAttribute('aria-label',labels.details);thumbnail.tabIndex=0;thumbnail.style.cursor='zoom-in';thumbnail.addEventListener('click',toggle);thumbnail.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' ')toggle(e);if(e.key==='Escape')close();});}

            if(card)new MutationObserver(function(){if(!card.classList.contains('kviz-radio_active'))close();}).observe(card,{attributes:true,attributeFilter:['class']});
            root.addEventListener('change',function(e){if(choice&&e.target.name===choice.name&&!choice.checked)close();});

            icon.addEventListener('click',toggle);icon.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' ')toggle(e);if(e.key==='Escape'){pop.classList.remove('calculator-info-open');icon.setAttribute('aria-expanded','false');}});
        });
        var desktop = window.innerWidth >= 1000;
        var firstTab=filter.querySelector('[data-calculator-step="0"]');
        var improvements=root.querySelector('.additional-image');

        var stepHeading=document.createElement('h3');stepHeading.className='calculator-step-heading';scroller.before(stepHeading);
        var fullTitles=[labels.photoTitle,labels.qualityTitle,labels.frameTitle,labels.extrasTitle];
        function positionUpload(){
            if(!edgeControls)return;editorHost.appendChild(edgeControls);
            if(!commentPanel)return;
            var caption=improvements&&improvements.querySelector('.popup-log-check p');
            if(caption)caption.textContent=labels.improvePhoto;

        }

        var commentPanel=root.querySelector('#userComment').closest('.accordion-content');
        var commentTitle=commentPanel.previousElementSibling;
        commentPanel.classList.add('calculator-comment-panel');commentTitle.classList.add('calculator-comment-title');
        var commentInput=root.querySelector('#userComment'),commentLabel=commentPanel.querySelector('.input-comments>label');
        commentInput.placeholder=labels.orderComment;
        commentInput.setAttribute('aria-label',commentInput.placeholder);commentLabel.hidden=true;
        var commentKey='viarcanvas:comment:'+location.pathname;
        try{var previousComment=sessionStorage.getItem(commentKey);if(previousComment!==null)commentInput.value=previousComment;}catch(e){}
        commentInput.addEventListener('input',function(){try{sessionStorage.setItem(commentKey,commentInput.value);}catch(e){}});

        commentTitle.hidden=true;commentTitle.removeAttribute('data-calculator-panel');
        commentPanel.removeAttribute('data-calculator-panel');commentPanel.classList.remove('calculator-panel-hidden');
        commentPanel.classList.add('calculator-footer-comment');
        var orderNotes=document.createElement('div');orderNotes.className='calculator-footer-notes';
        if(improvements){improvements.removeAttribute('data-calculator-panel');improvements.classList.remove('calculator-panel-hidden');orderNotes.appendChild(improvements);}
        orderNotes.appendChild(commentPanel);
        var orderFooter=root.querySelector('.formalizaton__submit');
        var orderCheckout=document.createElement('div');orderCheckout.className='calculator-footer-checkout';
        Array.from(orderFooter.children).forEach(function(child){orderCheckout.appendChild(child);});
        orderFooter.append(orderNotes,orderCheckout);
        orderCheckout.appendChild(frameConfirmation);
        // Keep the original service inputs and pricing path; replace presentation only.
        if(improvements){
            var prepText=JSON.parse(document.getElementById('calculator-preparation-text').textContent);
            improvements.hidden=true;improvements.classList.add('calculator-preparation-source');
            var preparation=document.createElement('section');preparation.className='calculator-preparation';preparation.dataset.calculatorPanel='4';scroller.prepend(preparation);
            var prepTitle=document.createElement('h3');prepTitle.textContent=prepText.title;var prepSubtitle=document.createElement('p');prepSubtitle.textContent=prepText.subtitle;preparation.append(prepTitle,prepSubtitle);
            var prepList=document.createElement('div');prepList.className='calculator-preparation-list';prepList.setAttribute('role','radiogroup');prepList.setAttribute('aria-label',prepText.title);preparation.appendChild(prepList);
            var preparationComment=document.createElement('div');preparationComment.className='calculator-preparation-comment';
            var preparationCommentInput=document.createElement('textarea');preparationCommentInput.id='preparationComment';preparationCommentInput.placeholder=labels.photoComment;preparationCommentInput.setAttribute('aria-label',labels.photoComment);preparationCommentInput.rows=3;
            var preparationCommentKey='viarcanvas:photo-comment:'+location.pathname;
            try{preparationCommentInput.value=sessionStorage.getItem(preparationCommentKey)||'';}catch(e){}
            preparationCommentInput.addEventListener('input',function(){try{sessionStorage.setItem(preparationCommentKey,preparationCommentInput.value);}catch(e){}});
            preparationComment.appendChild(preparationCommentInput);preparation.appendChild(preparationComment);

            var openPreparationDetails=null;
            var prepToggle=improvements.querySelector('.popup-log-check'),prepInputs=Array.from(improvements.querySelectorAll('input[name="boxes2[]"]')),prepChoices=[];
            function prepPrice(input){return !input?prepText.no_charge:Number(input.value)===0?prepText.included:'+ '+money.format(Number(input.value));}
            function prepSync(){var enabled=prepToggle.classList.contains('log-check_active');prepChoices.forEach(function(choice){var on=choice.input?enabled&&choice.input.checked:!enabled;choice.button.setAttribute('aria-checked',String(on));choice.button.tabIndex=on?0:-1;choice.card.classList.toggle('is-selected',on);});}
            function prepSelect(input){var enabled=!!input;prepToggle.classList.toggle('log-check_active',enabled);var save=prepToggle.querySelector('input[name="save"]');save.checked=enabled;if(input){prepInputs.forEach(function(i){i.checked=i===input;i.closest('.kviz-radio').classList.toggle('kviz-radio_active',i===input);});input.dispatchEvent(new Event('change',{bubbles:true}));}save.dispatchEvent(new Event('change',{bubbles:true}));prepSync();}
            prepInputs.slice(0,1).concat([null],prepInputs.slice(1)).forEach(function(input){
                var old=input&&input.closest('.kviz-radio'),name=input?(old.querySelector('.kviz-label__title')?.textContent.trim()||input.dataset.name):prepText.none;
                var data=input?(prepText.services[input.dataset.id]||{description:'',features:[],full:[]}):{description:prepText.none_description,features:prepText.none_features,full:prepText.none_features};
                var card=document.createElement('article');card.className='calculator-preparation-card';var select=document.createElement('button');select.type='button';select.className='calculator-preparation-select';select.setAttribute('role','radio');
                var title=document.createElement('strong');title.textContent=name;var price=document.createElement('span');price.className='calculator-preparation-price';price.textContent=prepPrice(input);var description=document.createElement('p');description.textContent=data.description;var list=document.createElement('ul');data.features.forEach(function(t){var li=document.createElement('li');li.textContent=t;list.appendChild(li);});var preview=document.createElement('span');preview.className='calculator-preparation-preview';preview.setAttribute('aria-hidden','true');
                var previewPicture=old&&old.querySelector('.pic-pop picture');
                if(previewPicture){var previewCopy=previewPicture.cloneNode(true);previewCopy.querySelectorAll('img').forEach(function(img){img.alt='';img.loading='lazy';});preview.appendChild(previewCopy);}
                else{preview.classList.add('is-original');preview.innerHTML='<svg viewBox="0 0 160 100" fill="none" aria-hidden="true"><rect x="30" y="12" width="100" height="76" rx="8" stroke="currentColor" stroke-width="3"/><circle cx="100" cy="33" r="8" fill="currentColor" opacity=".35"/><path d="m32 73 29-30 23 23 14-12 30 27" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/></svg>';}
                var previewCaption=document.createElement('span');previewCaption.className='calculator-preparation-preview-caption';previewCaption.textContent=previewPicture?labels.prepPreview:labels.prepOriginal;preview.appendChild(previewCaption);
                select.append(title,price,preview,description);select.addEventListener('click',function(){prepSelect(input);});
                var details=document.createElement('button');details.type='button';details.className='calculator-preparation-details';details.textContent=prepText.details_short+' ⌄';details.setAttribute('aria-expanded','false');
                var expansion=document.createElement('div');expansion.className='calculator-preparation-expansion';expansion.id='preparation-expansion-'+(input?input.dataset.id:'none');expansion.hidden=true;details.setAttribute('aria-controls',expansion.id);
                var h=document.createElement('h3');h.textContent=name+' · '+prepPrice(input);expansion.appendChild(h);
                function paragraph(heading,text){var sub=document.createElement('h4');sub.textContent=heading;var p=document.createElement('p');p.textContent=text;expansion.append(sub,p);}
                paragraph(prepText.suitable,data.description);
                var heading=document.createElement('h4');heading.textContent=prepText.contents;var fullList=document.createElement('ul');data.full.forEach(function(t){var li=document.createElement('li');li.textContent=t;fullList.appendChild(li);});expansion.append(heading,fullList);
                paragraph(prepText.limits,prepText.limits_text);
                if(previewPicture){paragraph(prepText.before_after,prepText.example);var example=previewPicture.cloneNode(true);example.querySelectorAll('img').forEach(function(img){img.alt=prepText.before_after+' — '+name;img.loading='lazy';});expansion.appendChild(example);}
                else paragraph(prepText.before_after,prepText.no_example);
                var collapse=document.createElement('button');collapse.type='button';collapse.className='calculator-preparation-collapse';collapse.textContent=prepText.collapse+' ⌃';expansion.appendChild(collapse);
                function closeDetails(){expansion.hidden=true;details.setAttribute('aria-expanded','false');details.textContent=prepText.details_short+' ⌄';if(openPreparationDetails===closeDetails)openPreparationDetails=null;}
                details.addEventListener('click',function(){if(!expansion.hidden){closeDetails();return;}if(openPreparationDetails)openPreparationDetails();expansion.hidden=false;details.setAttribute('aria-expanded','true');details.textContent=prepText.collapse+' ⌃';openPreparationDetails=closeDetails;});
                collapse.addEventListener('click',function(){closeDetails();details.focus({preventScroll:true});});
                card.append(select,details,expansion);prepList.appendChild(card);prepChoices.push({input:input,button:select,card:card});
            });

            prepList.addEventListener('keydown',function(e){if(!e.target.matches('.calculator-preparation-select'))return;var index=prepChoices.findIndex(function(c){return c.button===e.target;}),next=index;if(e.key==='ArrowDown'||e.key==='ArrowRight')next=(index+1)%prepChoices.length;else if(e.key==='ArrowUp'||e.key==='ArrowLeft')next=(index+prepChoices.length-1)%prepChoices.length;else return;e.preventDefault();prepSelect(prepChoices[next].input);prepChoices[next].button.focus();});root.addEventListener('change',prepSync);
            // Default only once on initial construction; later customer choices stay intact.
            var basicPreparation=prepInputs.find(function(input){return input.dataset.id==='1'&&Number(input.value)===0;});
            if(basicPreparation)prepSelect(basicPreparation);else prepSync();
        }
        // Keep panel identities stable while changing the customer-facing sequence.
        var stepOrder=[1,4,3,2];
        var preparationTab=document.createElement('button');preparationTab.type='button';preparationTab.className='jcf-ignore';preparationTab.dataset.calculatorStep='4';
        var preparationNumber=document.createElement('span');preparationNumber.textContent='2';preparationTab.append(preparationNumber,document.createTextNode(prepText.title));
        filter.querySelector('[data-calculator-step="3"]').before(preparationTab);
        var frameTab=filter.querySelector('[data-calculator-step="2"]');frameTab.parentElement.appendChild(frameTab);
        var isRussian=(document.documentElement.lang||'ru').startsWith('ru');
        frameTab.innerHTML='<span>3</span>'+(ui.packaging);
        fullTitles[2]=ui.packaging;
        var frameDialog=document.createElement('section');frameDialog.className='calculator-frame-inline';frameDialog.setAttribute('aria-labelledby','calculator-frame-dialog-title');
        var frameClose=document.createElement('button');frameClose.type='button';frameClose.className='calculator-frame-close';frameClose.textContent=ui.close+' ×';
        var frameHeading=document.createElement('h2');frameHeading.id='calculator-frame-dialog-title';frameHeading.textContent=ui.choose_frame;
        frameDialog.append(frameHeading);
        var framePreview=document.createElement('div');framePreview.className='calculator-frame-preview';
        var frameCanvas=document.createElement('canvas');frameCanvas.className='calculator-frame-canvas';frameCanvas.setAttribute('role','img');frameCanvas.setAttribute('aria-label',ui.framed_photo);
        var frameCaption=document.createElement('p');frameCaption.className='calculator-frame-preview-caption';framePreview.append(frameCanvas,frameCaption);frameDialog.appendChild(framePreview);
        Array.from(scroller.querySelectorAll('[data-calculator-panel="2"]')).forEach(function(el){el.removeAttribute('data-calculator-panel');el.classList.remove('calculator-panel-hidden');if(el.classList.contains('accordion-title'))el.hidden=true;else frameDialog.appendChild(el);});
        root.querySelector('.slider-tabs').appendChild(frameDialog);
        var structureText=JSON.parse(document.getElementById('calculator-frame-structure-text').textContent);
        var structure=document.createElement('details');structure.className='calculator-frame-structure';
        var structureSummary=document.createElement('summary');structureSummary.textContent=structureText[0];structure.appendChild(structureSummary);
        var structureBody=document.createElement('div');structureBody.className='calculator-frame-structure-body';
        structureBody.innerHTML='<svg viewBox="0 0 540 300" aria-hidden="true" focusable="false"><defs><linearGradient id="frame-wood" x2="1" y2="1"><stop stop-color="#e9c99b"/><stop offset="1" stop-color="#b88b53"/></linearGradient><linearGradient id="frame-gold" x2="1" y2="1"><stop stop-color="#ecd7ae"/><stop offset=".5" stop-color="#997444"/><stop offset="1" stop-color="#e3c696"/></linearGradient></defs><path d="M62 55 202 28 202 220 62 249Z M77 69 187 48 187 206 77 227Z" fill="url(#frame-wood)" fill-rule="evenodd" stroke="#af8557" stroke-width="2"/><path d="m69 88 124-24m-124 52 124-24m-124 109 124-24" stroke="#b88b53" opacity=".3"/><path d="m210 51 128 22m-128 146 128 22" stroke="#c9bcae" stroke-dasharray="5 5"/><path d="m177 79 140-27 0 192-140 27Z" fill="#fffaf1" stroke="#d4c9b9" stroke-width="2"/><path d="m188 88 118-23 0 167-118 23Z" fill="#dce5df"/><path d="m188 203 42-68 32 30 44-64v131l-118 23Z" fill="#809b8c"/><path d="m188 226 62-49 56 32v23l-118 23Z" fill="#b8c7a3"/><ellipse cx="278" cy="99" rx="12" ry="14" fill="#f0cd81"/><path d="m324 83 130-25m-130 153 130-25" stroke="#c9bcae" stroke-dasharray="5 5"/><path d="m328 65 155-30 0 211-155 30Z m18 15 119-23 0 174-119 23Z" fill="url(#frame-gold)" fill-rule="evenodd" stroke="#8a6b43" stroke-width="2"/><path d="m333 70 145-28 0 200-145 28Z" fill="none" stroke="#f5dfb7" stroke-width="2"/><path d="m328 65 18 15m137-45-18 22m18 189-18-15m-137 45 18-22" stroke="#795f40"/><g fill="#cf7049" stroke="white" stroke-width="2"><circle cx="474" cy="46" r="15"/><circle cx="307" cy="64" r="15"/><circle cx="70" cy="58" r="15"/></g><g fill="white" font-family="sans-serif" font-size="16" text-anchor="middle"><text x="474" y="52">1</text><text x="307" y="70">2</text><text x="70" y="64">3</text></g></svg>';
        var structureList=document.createElement('ol');structureText.slice(1,4).forEach(function(text){var item=document.createElement('li');item.textContent=text;structureList.appendChild(item);});var structureNote=document.createElement('p');structureNote.textContent=structureText[4];structureBody.append(structureList,structureNote);structure.appendChild(structureBody);frameDialog.querySelector('.frames').appendChild(structure);

        packaging.dataset.calculatorPanel='2';extras.dataset.calculatorPanel='2';
        commentPanel.dataset.calculatorPanel='2';scroller.appendChild(commentPanel);
        orderNotes.hidden=true;
        root.querySelector('.photo-content').appendChild(orderFooter);
        root.classList.add('calculator-comment-in-step');
        var frameTabItem=document.createElement('li'),frameOpen=document.createElement('button');frameOpen.type='button';frameOpen.className='calculator-frame-open';frameOpen.textContent=labels.frameShort;frameOpen.setAttribute('aria-selected','false');frameTabItem.appendChild(frameOpen);root.querySelector('.slider-tabs>.tabs').appendChild(frameTabItem);
        frameOpen.addEventListener('click',function(e){if(!frameGrid.querySelector('[data-deferred-frame]')){mountFrames(21);filterFrameCatalogue();syncFrames();}e.preventDefault();e.stopImmediatePropagation();root.classList.add('is-frame-selection');root.dataset.previewMode='room';root.querySelectorAll('.slider-tabs a[data-tabs]').forEach(function(a){a.classList.remove('active');a.setAttribute('aria-selected','false');});frameOpen.classList.add('active');frameOpen.setAttribute('aria-selected','true');root.dispatchEvent(new Event('frame-view-open'));});


        new MutationObserver(function(){if(root.dataset.previewMode==='room'&&!root.classList.contains('is-frame-selection')&&current!==1)show(1,false);}).observe(root,{attributes:true,attributeFilter:['data-preview-mode','class']});
        function adjacentStep(direction){return stepOrder[Math.max(0,Math.min(stepOrder.length-1,stepOrder.indexOf(current)+direction))];}
        var current = 1;
        function show(step, focus) {
            current = Math.max(1, Math.min(4, step));

            positionUpload();
            root.dataset.activeStep=current;
            var actionCart=root.querySelector('#t3_submit_btn');if(actionCart)actionCart.hidden=current!==2;
            stepHeading.textContent=fullTitles[current];stepHeading.hidden=current!==2;stepHeading.style.display=current!==2?'none':'';
            scroller.classList.toggle('size-step',current===1);
            scroller.scrollTop=0;
            requestAnimationFrame(fitSizeRows);
            var sizePanel=root.querySelector('.accordion__sizes-blocks').closest('.accordion-content');
            sizePanel.classList.add('calculator-sizes-panel');
            if(current===1)requestAnimationFrame(function(){var selected=sizePanel.querySelector('.kviz-radio_active');if(selected){var a=selected.getBoundingClientRect(),b=sizePanel.getBoundingClientRect();sizePanel.scrollTop+=(a.top-b.top)/layoutScale()-3;}});
            syncUpload();if(stickyNext){stickyNext.textContent=current===2?cartButton.textContent:nextButton.textContent;}
            filter.querySelectorAll('[data-calculator-panel]').forEach(function (element) {
                var panelStep=Number(element.dataset.calculatorPanel);
                var visible = panelStep === current;
                element.classList.toggle('calculator-panel-hidden', !visible);
                element.classList.toggle('calculator-panel-visible', visible);
            });
            root.querySelectorAll('[data-calculator-step]').forEach(function (button) {
                var index=Number(button.dataset.calculatorStep);
                button.hidden=index===0;
                button.querySelector('span').textContent=stepOrder.indexOf(index)+1;
                button.setAttribute('aria-label',button.textContent.trim());
                var active = index === current;
                button.setAttribute('aria-current', active ? 'step' : 'false');
                if (active && focus) button.focus({preventScroll:true});
            });
            root.querySelector('[data-calculator-back]').disabled = current === 1;
            root.querySelector('[data-calculator-next]').hidden = current === 2;
            if(focus&&!desktop)requestAnimationFrame(function(){filter.scrollIntoView({block:'start'});});
            if(root.classList.contains('calculator-fit')) requestAnimationFrame(function(){root.scrollIntoView({block:'start'});});
        }
        root.addEventListener('click', function (e) {
            var tab = e.target.closest('[data-calculator-step]');
            if (tab) show(Number(tab.dataset.calculatorStep), false);
            if (e.target.closest('[data-calculator-next]')) show(adjacentStep(1), true);
            if (e.target.closest('[data-calculator-back]')) show(adjacentStep(-1), true);
        });
        // Overlay is DOM-only: it never enters WebGL textures or order exports.
        var editorHost=root.querySelector('#PhotoEditor'),surface=editorHost.querySelector('.canvas_container');
        var overlay=document.createElement('button');overlay.type='button';overlay.className='calculator-photo-overlay jcf-ignore';
        overlay.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M7 18H5a4 4 0 0 1-.6-8A7 7 0 0 1 18 8a5 5 0 0 1 1 10h-2M12 20V9m-4 4 4-4 4 4"/></svg><strong></strong><small></small>';
        editorHost.appendChild(overlay);
        var expandPreview=document.createElement('button');expandPreview.type='button';expandPreview.className='calculator-expand-preview jcf-ignore';
        expandPreview.setAttribute('aria-label',labels.fullscreenPreview);expandPreview.title=labels.fullscreenPreview;
        expandPreview.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 3H3v6m12-6h6v6M3 15v6h6m12-6v6h-6"/></svg>';
        ['mousedown','pointerdown','touchstart'].forEach(function(type){expandPreview.addEventListener(type,function(e){e.stopPropagation();});});
        var expandHost=root.querySelector('.slider-tabs>.tabs-content');expandHost.style.position='relative';expandHost.appendChild(expandPreview);
        var expandedPreview=document.createElement('dialog');expandedPreview.className='calculator-expanded-preview';expandedPreview.setAttribute('aria-label',labels.fullscreenPreview);
        var expandedImage=document.createElement('img');expandedImage.alt=labels.fullscreenPreview;
        var closeExpanded=document.createElement('button');closeExpanded.type='button';closeExpanded.className='jcf-ignore';closeExpanded.textContent='×';closeExpanded.setAttribute('aria-label',labels.closePreview);
        expandedPreview.append(expandedImage,closeExpanded);document.body.appendChild(expandedPreview);
        var previousOverflow='';
        expandPreview.addEventListener('click',function(){
            var scene=editorHost.querySelector('canvas');if(!scene)return;
            expandedImage.src=scene.toDataURL('image/png');
            previousOverflow=document.body.style.overflow;document.body.style.overflow='hidden';
            expandedPreview.showModal();closeExpanded.focus();
        });
        closeExpanded.addEventListener('click',function(){expandedPreview.close();});
        expandedPreview.addEventListener('close',function(){document.body.style.overflow=previousOverflow;expandedImage.removeAttribute('src');expandPreview.focus({preventScroll:true});});

        var uploadStatus=document.createElement('p');uploadStatus.className='calculator-upload-status';uploadStatus.setAttribute('role','status');editorHost.after(uploadStatus);
        function openPhoto(){var image=root.querySelector('#imgs .loader-canvas button img');if(image)image.click();}
        overlay.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();openPhoto();});
        function syncOverlay(){
            var loaded=!!root.querySelector('#imgs .is-load img'),busy=root.querySelector('#imgs').dataset.uploadBusy==='true';
            overlay.classList.toggle('has-photo',loaded);overlay.disabled=busy;if(stickyNext)stickyNext.disabled=busy;overlay.setAttribute('aria-busy',String(busy));
            overlay.querySelector('strong').textContent=busy?labels.uploading:loaded?labels.replacePhoto:labels.upload;
            overlay.setAttribute('aria-label',busy?labels.uploading:labels.upload);
            overlay.querySelector('small').textContent=loaded?'':labels.uploadHint;
            var error=document.getElementById('canvas-upload-error');uploadStatus.textContent=error?error.textContent:'';
        }
        new MutationObserver(syncOverlay).observe(root.querySelector('#imgs'),{subtree:true,childList:true,attributes:true,attributeFilter:['class','src','data-upload-busy']});syncOverlay();
        var edgeControls=root.querySelector('#canvas-wrap-controls'),edgeHome=document.createComment('edge controls home');edgeControls.before(edgeHome);
        var edgeName=document.createElement('button');edgeName.type='button';edgeName.className='calculator-edge-name jcf-ignore';edgeName.setAttribute('aria-expanded','false');
        var edgeDetails=document.createElement('p');edgeDetails.className='calculator-edge-details';edgeDetails.hidden=true;editorHost.after(edgeName,edgeDetails);
        function syncEdge(){var input=edgeControls.querySelector('input:checked');var choice=input&&input.closest('label');if(!choice)return;edgeName.textContent=choice.querySelector('.edge-caption').textContent+' ⓘ';edgeDetails.textContent=choice.title;}
        edgeName.addEventListener('click',function(){edgeDetails.hidden=!edgeDetails.hidden;edgeName.setAttribute('aria-expanded',String(!edgeDetails.hidden));});root.addEventListener('change',syncEdge);syncEdge();

        // Intersection-driven mobile price dock, forwarding actions to original controls.
        var footer=root.querySelector('.formalizaton__submit'),cartButton=footer.querySelector('#t3_submit_btn');
        if(cartButton){cartButton.setAttribute('role','button');cartButton.tabIndex=0;cartButton.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();cartButton.click();}});}
        var nextButton=root.querySelector('[data-calculator-next]');
        var dock=document.createElement('div');dock.className='calculator-price-dock';dock.hidden=true;
        var dockLabel=document.createElement('div');dockLabel.textContent=labels.total;
        var stickyPrice=document.createElement('strong');stickyPrice.setAttribute('aria-live','polite');dockLabel.appendChild(stickyPrice);
        var stickyNext=document.createElement('button');stickyNext.type='button';stickyNext.className='jcf-ignore';dock.append(dockLabel,priceTrigger(),stickyNext);root.appendChild(dock);
        stickyNext.addEventListener('click',function(){if(current===2){if(cartButton)cartButton.click();}else show(adjacentStep(1),true);});
        function updateDock(){var vv=window.visualViewport;root.style.setProperty('--dock-keyboard',vv?Math.max(0,window.innerHeight-vv.height-vv.offsetTop)+'px':'0px');var preview=root.querySelector('.slider-tabs').getBoundingClientRect(),area=root.getBoundingClientRect(),f=footer.getBoundingClientRect();var visible=window.innerWidth<1000&&preview.bottom<=0&&area.bottom>0&&area.top<window.innerHeight&&!(f.top<window.innerHeight&&f.bottom>0);dock.hidden=!visible;document.body.classList.toggle('calculator-working',area.bottom>0&&area.top<window.innerHeight);root.classList.toggle('has-price-dock',visible);stickyNext.textContent=current===2?(cartButton?cartButton.textContent:labels.cart):nextButton.textContent;}
        var visibilityObserver=new IntersectionObserver(updateDock,{threshold:[0,.01,1]});[root,root.querySelector('.slider-tabs'),footer].forEach(function(el){visibilityObserver.observe(el);});window.addEventListener('resize',updateDock);var dockScheduled=false;window.addEventListener('scroll',function(){if(!dockScheduled){dockScheduled=true;requestAnimationFrame(function(){dockScheduled=false;updateDock();});}},{passive:true});new ResizeObserver(updateDock).observe(root);if(window.visualViewport)window.visualViewport.addEventListener('resize',updateDock);
        // Observe existing shared controls, never duplicate their price calculations.
        root.addEventListener('change', function () { setTimeout(updateSummary, 0); });
        root.addEventListener('click', function () { setTimeout(updateSummary, 250); });
        new MutationObserver(updateSummary).observe(root.querySelector('.totalPriceNew'), {childList:true, subtree:true, attributes:true});
        show(1, false); updateSummary();
        function fitSizeRows(){
            if(window.innerWidth>=1000)return;
            var panel=root.querySelector('.calculator-sizes-panel');if(!panel || !(current===1))return;
            panel.style.maxHeight='none';
            if(root.classList.contains('sizes-expanded'))return;
            var height=panel.clientHeight;
            var card=panel.querySelector('.accordion__sizes-block:not(.hidden-block) .calcSize');
            var rowHeight=card?card.getBoundingClientRect().height/layoutScale():90;
            var rows=Math.max(1,Math.floor((height-4+7)/(rowHeight+7)));
            panel.style.maxHeight=(rows*(rowHeight+7)-7+4)+'px';
        }
        new ResizeObserver(function(){requestAnimationFrame(fitSizeRows);}).observe(filter);
        new ResizeObserver(function(){root.style.setProperty('--frame-footer-height',footer.getBoundingClientRect().height/layoutScale()+'px');}).observe(footer);
        var mobileFitPending=false;
        function fitPinnedFrame(){
            if(mobileFitPending)return;mobileFitPending=true;
            requestAnimationFrame(function(){
                mobileFitPending=false;if(!root.classList.contains('mobile-frame-pinned'))return;
                var vv=window.visualViewport,visibleHeight=vv?vv.height:window.innerHeight;
                var preview=editorHost.getBoundingClientRect(),bottom=footer.getBoundingClientRect().bottom;
                var target=Math.max(60,Math.min(390,preview.height+visibleHeight-16-bottom));
                var value=Math.floor(target/layoutScale())+'px';
                if(root.style.getPropertyValue('--mobile-frame-preview-height')!==value)root.style.setProperty('--mobile-frame-preview-height',value);
            });
        }
        new ResizeObserver(fitPinnedFrame).observe(footer);
        if(window.visualViewport)window.visualViewport.addEventListener('resize',function(){measure();fitPinnedFrame();});
        function measure(){
            var nextDesktop=window.innerWidth>=1000;
            var stepNav=root.querySelector('.calculator-steps');
            // Step navigation belongs to its settings panel on every viewport.
            var desktopHeader=root.querySelector('.calculator-desktop-header');
            if(frameDialog){var frameHome=nextDesktop?root.querySelector('.photo-content'):root.querySelector('.slider-tabs');if(nextDesktop&&frameDialog.parentElement!==frameHome)frameHome.appendChild(frameDialog);else if(!nextDesktop&&frameDialog.parentElement===root.querySelector('.photo-content'))frameHome.appendChild(frameDialog);}
            if(framePreview){var previewHome=nextDesktop?root.querySelector('.slider-tabs'):frameDialog;if(framePreview.parentElement!==previewHome){if(nextDesktop)previewHome.appendChild(framePreview);else previewHome.insertBefore(framePreview,frameHeading.nextSibling);}}
            var navHome=nextDesktop&&desktopHeader?desktopHeader:filter;
            if(stepNav&&stepNav.parentElement!==navHome)navHome.prepend(stepNav);
            if(nextDesktop!==desktop){desktop=nextDesktop;show(current,false);}
            var header=document.querySelector('header')||document.querySelector('[role="banner"]');
            var top=header?Math.max(0,header.getBoundingClientRect().bottom):0;
            var available=(window.visualViewport?window.visualViewport.height:window.innerHeight)-top-16;
            if(pinSpacer){
                var desktopPinned=nextDesktop&&available>=360&&root.classList.contains('preview-pinned');
                var mobileFramePinned=!nextDesktop&&root.classList.contains('preview-pinned')&&root.classList.contains('is-frame-selection');
                if(desktopPinned||mobileFramePinned){
                    var rect=(root.classList.contains('desktop-pinned')||root.classList.contains('mobile-frame-pinned')?pinSpacer:root).getBoundingClientRect();
                    pinSpacer.style.height=(root.getBoundingClientRect().height||available)+'px';pinSpacer.hidden=false;
                    root.style.setProperty('--pinned-left',rect.left/layoutScale()+'px');root.style.setProperty('--pinned-width',rect.width/layoutScale()+'px');
                }else pinSpacer.hidden=true;
                root.classList.toggle('desktop-pinned',desktopPinned);
                if(root.classList.contains('mobile-frame-pinned')!==mobileFramePinned)root.classList.toggle('mobile-frame-pinned',mobileFramePinned);
            }

            root.style.setProperty('--calculator-top',(top+8)/layoutScale()+'px');
            root.style.setProperty('--calculator-height',available/layoutScale()+'px');
            root.classList.toggle('calculator-fit',window.innerWidth>=1000&&available>=360);
            fitPinnedFrame();
        }
        // The legacy background used 1380px from the section bottom. A compact
        // calculator must not pull that dark background behind the intro text.
        var retouch=root.closest('.retouch__screen');
        function placeRetouchBackground(){
            if(!retouch)return;
            var section=retouch.getBoundingClientRect(), form=root.getBoundingClientRect();
            retouch.style.setProperty('--calculator-background-height',Math.max(0,section.bottom-form.top+36)+'px');
        }
        if(retouch)new ResizeObserver(placeRetouchBackground).observe(retouch);
        window.addEventListener('resize',placeRetouchBackground);
        placeRetouchBackground();
        // Stop each arrival at the header baseline; release on the next wheel gesture.
        var arrivalHeld=false,arrivalReleased=false,lastArrivalWheel=0;
        function arrivalTop(){return (parseFloat(root.style.getPropertyValue('--calculator-top'))||0)*layoutScale();}
        window.addEventListener('wheel',function(e){
            if(window.innerWidth<1000||!root.classList.contains('calculator-fit')||root.classList.contains('desktop-pinned')||document.querySelector('dialog[open]')||e.ctrlKey||!e.deltaY)return;
            var now=performance.now(),delta=e.deltaY*(e.deltaMode===1?16:e.deltaMode===2?window.innerHeight:1);
            if(arrivalHeld){
                if(now-lastArrivalWheel<220){lastArrivalWheel=now;e.preventDefault();return;}
                arrivalHeld=false;arrivalReleased=true;
            }
            // Preserve nested scrolling only when that panel can consume this direction.
            var node=e.target instanceof Element?e.target:null;
            while(node&&node!==document.body){
                var style=getComputedStyle(node);
                if(/auto|scroll/.test(style.overflowY)&&node.scrollHeight>node.clientHeight+2&&((delta>0&&node.scrollTop+node.clientHeight<node.scrollHeight-2)||(delta<0&&node.scrollTop>2)))return;
                node=node.parentElement;
            }
            var offset=root.getBoundingClientRect().top-arrivalTop();
            if(arrivalReleased&&Math.abs(offset)<=8){if(e.target instanceof Element&&e.target.closest("#PhotoEditor")){e.preventDefault();e.stopImmediatePropagation();window.scrollBy({top:delta,behavior:"instant"});}return;}
            if(Math.abs(offset)>8)arrivalReleased=false;
            if((delta>0&&offset>0&&offset-delta<=0)||(delta<0&&offset<0&&offset-delta>=0)){
                e.preventDefault();arrivalHeld=true;lastArrivalWheel=now;
                e.stopImmediatePropagation();window.scrollBy({top:offset,behavior:'instant'});
            }
        },{passive:false,capture:true});
        window.addEventListener('keydown',function(e){if(e.key==='Escape'){arrivalHeld=false;arrivalReleased=true;}});
        // Reuse the live controls in a modal sheet; never clone order inputs.
        var mobileSheet=document.createElement('dialog');mobileSheet.className='calculator-mobile-sheet';mobileSheet.id='calculator-mobile-sheet';
        var sheetHeader=document.createElement('header'),sheetTitle=document.createElement('strong'),sheetClose=document.createElement('button');
        sheetTitle.id='calculator-mobile-sheet-title';mobileSheet.setAttribute('aria-labelledby',sheetTitle.id);
        sheetClose.type='button';sheetClose.textContent='×';sheetClose.setAttribute('aria-label',ui.close||labels.close||'Close');sheetHeader.append(sheetTitle,sheetClose);mobileSheet.appendChild(sheetHeader);root.appendChild(mobileSheet);
        var sheetTrigger=document.createElement('button');sheetTrigger.type='button';sheetTrigger.className='calculator-mobile-sheet-trigger';sheetTrigger.setAttribute('aria-haspopup','dialog');sheetTrigger.setAttribute('aria-controls',mobileSheet.id);root.querySelector('.slider-tabs').appendChild(sheetTrigger);
        var sheetNode=null,sheetHome=null,sheetKind='',savedBodyOverflow='';
        function mobileSheetMode(){return window.innerWidth<1000&&root.classList.contains('preview-pinned')&&(root.classList.contains('is-frame-selection')||current===1);}
        function restoreSheet(){
            if(sheetNode&&sheetHome){sheetHome.replaceWith(sheetNode);sheetNode=null;sheetHome=null;}
            document.body.style.overflow=savedBodyOverflow;sheetTrigger.setAttribute('aria-expanded','false');
        }
        function closeSheet(){if(mobileSheet.open){mobileSheet.close();restoreSheet();sheetTrigger.focus({preventScroll:true});}}
        function syncSheet(){
            var enabled=mobileSheetMode(),isFrame=root.classList.contains('is-frame-selection');
            if(root.classList.contains('mobile-frame-pinned')!==(window.innerWidth<1000&&root.classList.contains('preview-pinned')&&isFrame))measure();
            if(mobileSheet.open&&(!enabled||sheetKind!==(isFrame?'frame':'size')))closeSheet();
            if(root.classList.contains('mobile-sheet-mode')!==enabled)root.classList.toggle('mobile-sheet-mode',enabled);sheetTrigger.hidden=!enabled;
            var selected=isFrame?(frameGrid.querySelector('.calculator-demo-frame.is-preview')||frameGrid.querySelector('.ramu-item.active')||noFrame):root.querySelector('.accordion__sizes-block .kviz-radio_active');
            var value=isFrame?(selected.querySelector('.code')||selected.querySelector('strong')):selected&&selected.querySelector('.size-card-name');
            var text=isFrame?(selected.dataset.name||value&&value.textContent||selected.textContent.trim().split('\n').filter(Boolean).pop()):value&&value.textContent;
            var caption=(isFrame?labels.frameShort:labels.size||'Размер')+' · '+(text||'40 × 50 cm')+' ⌃';
            if(sheetTrigger.textContent!==caption)sheetTrigger.textContent=caption;
        }
        sheetTrigger.addEventListener('click',function(){
            if(!mobileSheetMode()||mobileSheet.open)return;
            sheetKind=root.classList.contains('is-frame-selection')?'frame':'size';sheetNode=sheetKind==='frame'?frameDialog:scroller;
            sheetHome=document.createComment('mobile sheet home');sheetNode.before(sheetHome);mobileSheet.appendChild(sheetNode);
            sheetTitle.textContent=sheetKind==='frame'?labels.frameShort:labels.size||'Размер';savedBodyOverflow=document.body.style.overflow;document.body.style.overflow='hidden';mobileSheet.showModal();sheetTrigger.setAttribute('aria-expanded','true');
        });
        sheetClose.addEventListener('click',closeSheet);mobileSheet.addEventListener('cancel',function(e){e.preventDefault();closeSheet();});
        mobileSheet.addEventListener('click',function(e){
            if(e.target===mobileSheet){var r=mobileSheet.getBoundingClientRect();if(e.clientY<r.top||e.clientX<r.left||e.clientX>r.right)closeSheet();return;}
            if(e.target.closest('.calculator-frame-zoom,.calculator-frame-details')||(e.target.closest('button')&&!e.target.closest('.calculator-demo-frame')))return;
            if(e.target.closest('.ramu-item,.calculator-demo-frame,.accordion__sizes-block .kviz-radio'))setTimeout(function(){closeSheet();syncSheet();},0);
        });
        root.addEventListener('change',function(){setTimeout(syncSheet,0);});root.addEventListener('click',function(){setTimeout(syncSheet,0);});
        new MutationObserver(syncSheet).observe(root,{attributes:true,attributeFilter:['class','data-active-step','data-preview-mode']});window.addEventListener('resize',syncSheet);syncSheet();
        // Desktop presentation only: keep the original controls and order state.
        var desktopHeader=document.createElement('div');desktopHeader.className='calculator-desktop-header';
        var desktopBrand=document.createElement('div');desktopBrand.className='calculator-desktop-brand';
        var desktopTitle=document.createElement('h2');desktopTitle.textContent=labels.title;desktopBrand.appendChild(desktopTitle);
        var desktopProduct=document.createElement('span');desktopProduct.className='calculator-desktop-product';
        var desktopLang=(document.documentElement.lang||'lv').slice(0,2);
        desktopProduct.textContent=desktopLang==='ru'?'Фото на холсте':desktopLang==='lv'?'Foto uz audekla':'Photo on canvas';
        desktopBrand.appendChild(desktopProduct);desktopHeader.appendChild(desktopBrand);root.prepend(desktopHeader);
        root.querySelectorAll('.accordion__sizes-block').forEach(function(group){group.querySelectorAll('.calcSize').forEach(function(card,index){if(index>=6)card.classList.add('desktop-size-overflow');});});
        var locale=(document.documentElement.lang||'en').slice(0,2);
        var qualityCopy=locale==='lv'?['Jūsu makets','Audekla faktūra','Stūri un malas','Aizmugure','Video','Rādīt vai paslēpt piemērus','Video drīzumā']:locale==='ru'?['Ваш макет','Фактура холста','Торцы и углы','Обратная сторона','Видео','Показать или скрыть примеры','Видео скоро появится']:['Your preview','Canvas texture','Edges and corners','Back side','Video','Show or hide examples','Video coming soon'];
        var quality=document.createElement('section');quality.className='calculator-desktop-quality';
        var qualityToggle=document.createElement('button');qualityToggle.type='button';qualityToggle.className='jcf-ignore calculator-quality-toggle';qualityToggle.setAttribute('aria-expanded','true');qualityToggle.setAttribute('aria-controls','calculator-quality-thumbs');qualityToggle.setAttribute('aria-label',qualityCopy[5]);
        qualityToggle.innerHTML='<span class="quality-chevron" aria-hidden="true">⌄</span>';
        var qualityThumbs=document.createElement('div');qualityThumbs.id='calculator-quality-thumbs';qualityThumbs.className='calculator-quality-thumbs';qualityThumbs.setAttribute('role','tablist');
        var qualityViewer=document.createElement('div');qualityViewer.className='calculator-quality-viewer';qualityViewer.id='calculator-quality-viewer';qualityViewer.hidden=true;qualityViewer.setAttribute('role','tabpanel');
        root.querySelector('.slider-tabs>.tabs-content').appendChild(qualityViewer);
        var qualitySources=['canvas.webp','canvas1.webp','canvas1.webp','fastening.webp','canvas.webp'];
        function selectQuality(index){
            qualityViewer.replaceChildren();qualityViewer.hidden=index===0;
            root.querySelectorAll('.calculator-quality-thumbs button').forEach(function(button,i){button.setAttribute('aria-selected',String(i===index));});
            if(index===0){return;}
            if(index===4){
                var videoSource=root.dataset.qualityVideo;
                if(!videoSource){var embed=document.createElement('iframe');embed.src='https://www.youtube.com/embed/blA6D8eS5vo?rel=0';embed.title=qualityCopy[4];embed.allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';embed.allowFullscreen=true;qualityViewer.appendChild(embed);return;}
                if(videoSource){var video=document.createElement('video');video.controls=true;video.preload='none';video.src=videoSource;qualityViewer.appendChild(video);}
                else{var note=document.createElement('div');note.className='quality-video-empty';note.textContent='▷ '+qualityCopy[6];qualityViewer.appendChild(note);}
            }else{var image=document.createElement('img');image.src='/theme/viar/images/canvas/'+qualitySources[index];image.alt=qualityCopy[index];image.className='quality-detail-'+index;qualityViewer.appendChild(image);}
        }
        qualityCopy.slice(0,5).forEach(function(title,index){
            var button=document.createElement('button');button.type='button';button.className='jcf-ignore';button.setAttribute('role','tab');button.setAttribute('aria-selected',String(index===0));button.setAttribute('aria-controls','calculator-quality-viewer');
            var visual=document.createElement('div');visual.className='quality-visual quality-visual-'+index;
            var img=document.createElement('img');img.src='/theme/viar/images/canvas/'+qualitySources[index];img.alt=title;img.loading='lazy';visual.appendChild(img);
            if(index===4){var play=document.createElement('i');play.className='quality-video-play';play.textContent='▶';visual.appendChild(play);}
            var caption=document.createElement('span');caption.textContent=title;button.append(visual,caption);qualityThumbs.appendChild(button);
            button.onclick=function(){root.querySelector('.slider-tabs a[data-tabs="tabs-item1"]').click();selectQuality(index);};
        });
        qualityToggle.onclick=function(){var open=qualityToggle.getAttribute('aria-expanded')==='true';qualityToggle.setAttribute('aria-expanded',String(!open));qualityThumbs.hidden=open;qualityToggle.querySelector('.quality-chevron').textContent=open?'⌃':'⌄';if(open){selectQuality(0);root.querySelector('.slider-tabs a[data-tabs="tabs-item1"]').click();}window.dispatchEvent(new Event('resize'));};
        root.querySelector('.slider-tabs>.tabs').addEventListener('click',function(){selectQuality(0);});
        window.addEventListener('resize',function(){if(innerWidth<1000)selectQuality(0);});
        quality.append(qualityToggle,qualityThumbs);root.querySelector('.slider-tabs').appendChild(quality);
        var qualityAlignPending=false;
        function alignQuality(){if(qualityAlignPending)return;qualityAlignPending=true;requestAnimationFrame(function(){qualityAlignPending=false;if(innerWidth<1000)return;var preview=root.querySelector('#PhotoEditor .canvas_container'),slider=root.querySelector('.slider-tabs');if(!preview||!preview.clientWidth)return;var r=preview.getBoundingClientRect(),s=slider.getBoundingClientRect(),scale=layoutScale();var inset=(parseFloat(getComputedStyle(slider).paddingLeft)||0)+(parseFloat(getComputedStyle(slider).borderLeftWidth)||0);quality.style.width=(r.width/scale)+'px';quality.style.marginLeft=Math.max(0,(r.left-s.left)/scale-inset)+'px';});}
        new ResizeObserver(alignQuality).observe(root.querySelector('#PhotoEditor .canvas_container'));window.addEventListener('resize',alignQuality);alignQuality();
        function syncDesktopSelection(){
            root.querySelectorAll('.accordion__sizes-block .calcSize').forEach(function(card){card.dataset.selectionLabel=ui.selected;});
            var active=root.querySelector('.accordion__sizes-block .kviz-radio_active .size-card-name');
            summary.textContent=labels.canvasShort+' · '+(active?active.textContent:'');
            var original=root.querySelector('#imgs .is-load img'),image=quality.querySelector('.quality-visual-0 img');
            var uploaded=!!(original&&original.getAttribute('src'));
            if(root.classList.contains('has-customer-photo')!==uploaded){root.classList.toggle('has-customer-photo',uploaded);if(uploaded)selectQuality(0);requestAnimationFrame(function(){window.dispatchEvent(new Event('resize'));});}
            image.hidden=false;var previewSource=original?original.src:'/theme/viar/images/canvas/canvas.webp';if(image.getAttribute('src')!==previewSource)image.src=previewSource;
        }
        root.addEventListener('click',function(){setTimeout(syncDesktopSelection,0);});root.addEventListener('change',syncDesktopSelection);syncDesktopSelection();
        var uploadList=root.querySelector('#imgs');if(uploadList)new MutationObserver(syncDesktopSelection).observe(uploadList,{subtree:true,childList:true,attributes:true,attributeFilter:['class','src']});
        measure();window.addEventListener('resize',measure);
        var header=document.querySelector('header');if(header)new ResizeObserver(measure).observe(header);
        document.addEventListener('click',function(e){var a=e.target.closest('a[href]');if(!a)return;
            if(['#generate','#generator','#generator_sizes2'].indexOf(a.getAttribute('href'))<0)return;
            e.preventDefault();if(a.getAttribute('href')==='#generator_sizes2')show(1,false);root.scrollIntoView({behavior:'smooth',block:'start'});
        });
        if(['#generate','#generator','#generator_sizes2'].includes(location.hash))requestAnimationFrame(function(){root.scrollIntoView({block:'start'});});
        window.addEventListener('load',function(){measure();if(['#generate','#generator','#generator_sizes2'].includes(location.hash))root.scrollIntoView({block:'start'});},{once:true});
        // Restore widgets as soon as the user leaves the calculator's working area.
        new IntersectionObserver(function(entries){document.body.classList.toggle('calculator-working',entries[0].isIntersecting);},{threshold:0}).observe(root);

    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
