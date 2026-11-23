/*! French Class Prestige — page de service (générée depuis la maquette Horizon) */
(function(){"use strict";
if(!window.FCP_HOME_URL)window.FCP_HOME_URL="/?page_id=737&preview=true";
var BASE="https://cdn.jsdelivr.net/gh/LUD973/horizon@8de40a144ac46faa19a27cf075f6c9a983e734cc/site/home-v3/",FONTS="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Jost:wght@300;400;500&display=swap",WA="https://wa.me/33656898611";
var LAYOUTS={d:{css:"body{margin:0}\n.sv{--navy:#0B1F3A;--deep:#050E1F;--gold:#C6A15B;--gold-d:#7A5C2B;--gold-l:#C9A867;--ivory:#F6F1E7;font-family:Jost,system-ui,sans-serif;color:#0B1F3A;background:#F6F1E7;-webkit-font-smoothing:antialiased}\n.sv{overflow-x:clip}\n.sv *{box-sizing:border-box}\n.sv .wrap>*,.sv .wrap>*>*{min-width:0;max-width:100%}\n.sv .serif{font-family:'Cormorant Garamond',Georgia,serif;font-weight:400}\n.sv .eb{font-size:11px;letter-spacing:.3em;text-transform:uppercase}\n.sv .wrap{width:100%;min-width:0;max-width:1200px;margin:0 auto;padding:0 clamp(20px,4.4vw,64px)}\n.sv .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;height:52px;padding:0 26px;border:0;border-radius:2px;background:#C6A15B;color:#0B1F3A;font-family:Jost,sans-serif;font-size:12px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;cursor:pointer;text-decoration:none}\n.sv .btn.ghost{background:transparent;color:inherit;border:1px solid currentColor}\n.sv .grid{display:grid;gap:clamp(14px,2vw,24px)}\n.sv .g2{grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr))}\n.sv .g4{grid-template-columns:repeat(auto-fit,minmax(min(100%,230px),1fr))}\n.sv .rail{display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:6px;scrollbar-width:none}\n.sv .rail::-webkit-scrollbar{display:none}\n.sv .rail>*{scroll-snap-align:start;flex:0 0 min(78%,280px)}\n.sv .desk{display:none}\n.sv .mob{display:flex}\n@media (min-width:900px){.sv .desk{display:flex}.sv .mob{display:none}.sv .rail{overflow:visible}.sv .rail>*{flex:1 1 0}}\n.sv details summary{list-style:none;cursor:pointer}\n.sv details summary::-webkit-details-marker{display:none}\n\n#fcp-home .sv *,#fcp-home .sv *:before,#fcp-home .sv *:after{box-sizing:border-box}\n#fcp-home .sv{position:relative}",tpl:"\n<div class=\"sv\" style=\"min-height: 100%; position: relative\">\n\n<header style=\"position: absolute; top: 0; left: 0; right: 0; z-index: 5; color: #F6F1E7\">\n<div class=\"wrap\" style=\"display: flex; align-items: center; justify-content: space-between; gap: 20px; height: clamp(84px,9vw,112px)\">\n<a href=\"{{homeUrl}}\" aria-label=\"Accueil\"><img decoding=\"async\" src=\"https://www.frenchclassprestige.com/wp-content/uploads/2026/11/fcp-logo-header.png\" alt=\"French Class Prestige \u00b7 Transport &amp; Conciergerie\" style=\"display: block; height: clamp(54px,5.4vw,74px); width: auto\"></a>\n<nav class=\"desk\" aria-label=\"Navigation principale\" style=\"gap: 30px; align-items: center; font-size: 12px; letter-spacing: .16em; text-transform: uppercase\"><sc-for list=\"{{nav}}\" as=\"n\" hint-placeholder-count=\"6\"><a href=\"#\" style=\"color: inherit; text-decoration: none\">{{n}}</a></sc-for></nav>\n<div class=\"desk\" style=\"gap: 12px; align-items: center\"><button type=\"button\" onClick=\"{{openBk}}\" class=\"btn\">R\u00e9server</button></div>\n<button type=\"button\" class=\"mob\" aria-label=\"Menu\" style=\"width: 46px; height: 46px; border-radius: 50%; border: 1px solid rgba(246,241,231,.4); background: transparent; color: #F6F1E7; align-items: center; justify-content: center\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M4 7h16M4 12h16M4 17h10\"/></svg></button>\n</div>\n</header>\n\n<section aria-labelledby=\"h1\" style=\"position: relative; min-height: clamp(640px,92vh,860px); color: #F6F1E7; display: flex; align-items: flex-end; overflow: hidden\">\n<img decoding=\"async\" src=\"https://cdn.jsdelivr.net/gh/LUD973/horizon@8de40a144ac46faa19a27cf075f6c9a983e734cc/site/home-v3/img/hero.jpg\" alt=\"Un chauffeur de la Maison accueille ses clients \u00e0 leur arriv\u00e9e\" style=\"position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover\">\n<div style=\"position: absolute; inset: 0; background: linear-gradient(180deg, rgba(5,14,31,.55) 0%, rgba(5,14,31,.15) 35%, rgba(5,14,31,.85) 78%, #050E1F 100%)\"></div>\n<div class=\"wrap\" style=\"position: relative; padding-top: 140px; padding-bottom: clamp(28px,5vw,64px); display: flex; flex-wrap: wrap; gap: clamp(28px,4vw,56px); align-items: flex-end; justify-content: space-between\">\n<div style=\"flex: 1 1 460px; max-width: 640px; display: flex; flex-direction: column; gap: 18px\">\n<nav aria-label=\"Fil d\u2019Ariane\" style=\"font-size: 12px; color: rgba(246,241,231,.7)\"><a href=\"{{homeUrl}}\" style=\"color: inherit; text-decoration: none\">Accueil</a> \u00b7 <a href=\"#\" style=\"color: inherit; text-decoration: none\">Transferts a\u00e9roport</a> \u00b7 <span style=\"color: #C9A867\">Paris-Le Bourget</span></nav>\n<span class=\"eb\" style=\"color: #C9A867\">Aviation d\u2019affaires \u00b7 Paris-Le Bourget</span>\n<h1 id=\"h1\" class=\"serif\" style=\"margin: 0; font-size: clamp(40px,5.6vw,72px); line-height: 1.02\">Votre chauffeur<br><em style=\"color: #C9A867\">au Bourget.</em></h1>\n<p style=\"margin: 0; font-size: clamp(16px,1.4vw,18px); font-weight: 300; line-height: 1.6; color: rgba(246,241,231,.88)\">Accueil \u00e0 la sortie du terminal de votre op\u00e9rateur d\u2019aviation d\u2019affaires, suivi de votre vol, tarif fixe connu \u00e0 l\u2019avance. Du Bourget \u00e0 votre adresse, en toute discr\u00e9tion.</p>\n<div style=\"display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 13px; color: rgba(246,241,231,.85)\"><sc-for list=\"{{proofs}}\" as=\"p\" hint-placeholder-count=\"3\"><span style=\"display: inline-flex; gap: 8px; align-items: center\"><span style=\"color: #C9A867\">\u2726</span>{{p}}</span></sc-for></div>\n</div>\n\n<div style=\"flex: 1 1 340px; max-width: 420px; background: rgba(246,241,231,.97); color: #0B1F3A; border-radius: 4px; padding: 22px; display: flex; flex-direction: column; gap: 16px; box-shadow: 0 30px 60px rgba(5,14,31,.35)\">\n<div style=\"display: flex; justify-content: space-between; align-items: baseline\"><span class=\"eb\" style=\"color: #7A5C2B\">Votre transfert</span><span style=\"font-size: 12px; color: rgba(11,31,58,.6)\">Tarif fixe TTC</span></div>\n<div role=\"group\" aria-label=\"Sens du trajet\" style=\"display: flex; padding: 4px; border: 1px solid rgba(11,31,58,.15); border-radius: 999px\"><sc-for list=\"{{dirs}}\" as=\"d\" hint-placeholder-count=\"2\"><button type=\"button\" onClick=\"{{d.pick}}\" aria-pressed=\"{{d.sel}}\" style=\"flex: 1; height: 40px; border: 0; border-radius: 999px; background: {{d.bg}}; color: {{d.fg}}; font-family: Jost, sans-serif; font-size: 12px; letter-spacing: .08em\">{{d.l}}</button></sc-for></div>\n<label style=\"display: flex; flex-direction: column; gap: 6px; font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: #7A5C2B\">{{addrLabel}}<input type=\"text\" name=\"addr\" autocomplete=\"street-address\" placeholder=\"Adresse, h\u00f4tel \u00e0 Paris\" style=\"height: 42px; border: 0; border-bottom: 1px solid rgba(11,31,58,.3); background: transparent; font-family: Jost, sans-serif; font-size: 16px; color: #0B1F3A; padding: 0; text-transform: none; letter-spacing: 0\"></label>\n<div style=\"display: flex; gap: 14px\"><label style=\"flex: 1; display: flex; flex-direction: column; gap: 6px; font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: #7A5C2B\">{{dateLabel}}<input type=\"date\" name=\"date\" style=\"height: 42px; border: 0; border-bottom: 1px solid rgba(11,31,58,.3); background: transparent; font-family: Jost, sans-serif; font-size: 15px; color: #0B1F3A; padding: 0\"></label><label style=\"flex: 1; display: flex; flex-direction: column; gap: 6px; font-size: 11px; letter-spacing: .2em; text-transform: uppercase; color: #7A5C2B\">Vol<input type=\"text\" name=\"flight\" placeholder=\"AF 007\" style=\"height: 42px; border: 0; border-bottom: 1px solid rgba(11,31,58,.3); background: transparent; font-family: Jost, sans-serif; font-size: 15px; color: #0B1F3A; padding: 0; text-transform: none; letter-spacing: 0\"></label></div>\n<div role=\"radiogroup\" aria-label=\"Gamme\" style=\"display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px\"><sc-for list=\"{{tiers}}\" as=\"t\" hint-placeholder-count=\"4\"><button type=\"button\" onClick=\"{{t.pick}}\" aria-pressed=\"{{t.sel}}\" style=\"display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 9px 2px; border-radius: 4px; border: 1px solid {{t.border}}; background: {{t.bg}}; color: {{t.fg}}; font-family: Jost, sans-serif\"><span style=\"font-size: 11px; letter-spacing: .04em\">{{t.n}}</span><span class=\"serif\" style=\"font-size: 19px; line-height: 1.1\">{{t.p}}</span></button></sc-for></div>\n<button type=\"button\" onClick=\"{{openBk}}\" class=\"btn\" style=\"width: 100%\">R\u00e9server \u00b7 {{price}}</button>\n<span style=\"font-size: 12px; font-weight: 300; line-height: 1.5; color: rgba(11,31,58,.65)\">Aucun paiement en ligne. La Maison confirme votre transfert, en g\u00e9n\u00e9ral sous 30 minutes.</span>\n</div>\n</div>\n</section>\n\n<section aria-labelledby=\"h-arr\" style=\"background: #050E1F; color: #F6F1E7; padding: clamp(56px,8vw,110px) 0\">\n<div class=\"wrap\" style=\"display: flex; flex-direction: column; gap: clamp(28px,4vw,48px)\">\n<div style=\"display: flex; flex-direction: column; gap: 12px; max-width: 720px\"><span class=\"eb\" style=\"color: #C9A867\">Votre arriv\u00e9e, pas \u00e0 pas</span><h2 id=\"h-arr\" class=\"serif\" style=\"margin: 0; font-size: clamp(32px,4vw,52px); line-height: 1.05\">De la passerelle \u00e0 votre porte,<br><em style=\"color: #C9A867\">nous nous occupons de tout.</em></h2></div>\n<ol class=\"rail\" style=\"list-style: none; margin: 0; padding: 0\"><sc-for list=\"{{steps}}\" as=\"s\" hint-placeholder-count=\"4\"><li style=\"display: flex; flex-direction: column; gap: 12px; padding: 24px 22px; border: 1px solid rgba(201,168,103,.28); border-radius: 4px; background: rgba(246,241,231,.03)\"><span class=\"serif\" style=\"font-size: 40px; line-height: 1; color: #C9A867\">{{s.n}}</span><span style=\"font-size: 17px\">{{s.t}}</span><span style=\"font-size: 14px; font-weight: 300; line-height: 1.6; color: rgba(246,241,231,.78)\">{{s.d}}</span></li></sc-for></ol>\n</div>\n</section>\n\n<section aria-labelledby=\"h-tar\" style=\"padding: clamp(56px,8vw,110px) 0\">\n<div class=\"wrap\" style=\"display: flex; flex-direction: column; gap: clamp(24px,3vw,40px)\">\n<div style=\"display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px\"><div style=\"display: flex; flex-direction: column; gap: 12px; max-width: 640px\"><span class=\"eb\" style=\"color: #7A5C2B\">Tarifs</span><h2 id=\"h-tar\" class=\"serif\" style=\"margin: 0; font-size: clamp(32px,4vw,52px); line-height: 1.05\">Un prix fixe,<br><em style=\"color: #7A5C2B\">annonc\u00e9 avant le d\u00e9part.</em></h2></div><p style=\"margin: 0; max-width: 380px; font-size: 14px; font-weight: 300; line-height: 1.6; color: rgba(11,31,58,.75)\">Paris intra-muros \u2194 Paris-Le Bourget, par v\u00e9hicule, toutes taxes comprises. P\u00e9ages, suivi de vol et temps d\u2019attente offert inclus. Le retour se r\u00e9serve au m\u00eame tarif.</p></div>\n<div class=\"grid g4\"><sc-for list=\"{{cards}}\" as=\"c\" hint-placeholder-count=\"4\"><div style=\"display: flex; flex-direction: column; background: #FFFFFF; border: 1px solid {{c.border}}; border-radius: 4px; overflow: hidden\"><div style=\"padding: 22px 22px 16px; display: flex; flex-direction: column; gap: 6px\"><span class=\"eb\" style=\"color: #7A5C2B\">{{c.n}}</span><span class=\"serif\" style=\"font-size: 44px; line-height: 1\">{{c.p}}</span><span style=\"font-size: 13px; font-weight: 300; color: rgba(11,31,58,.7)\">{{c.car}}</span></div><div style=\"margin-top: auto; padding: 14px 22px; border-top: 1px solid rgba(11,31,58,.08); display: flex; justify-content: space-between; font-size: 13px; color: rgba(11,31,58,.75)\"><span>{{c.cap}}</span><span>{{c.bag}}</span></div></div></sc-for></div>\n<div style=\"display: flex; flex-wrap: wrap; gap: 10px 26px; font-size: 13px; color: rgba(11,31,58,.75)\"><sc-for list=\"{{extras}}\" as=\"x\" hint-placeholder-count=\"3\"><span><span style=\"color: #7A5C2B\">\u2726</span> {{x}}</span></sc-for></div>\n</div>\n</section>\n\n\n<section aria-labelledby=\"h-faq\" style=\"background: #FFFFFF; padding: clamp(56px,8vw,110px) 0\">\n<div class=\"wrap\" style=\"display: flex; flex-wrap: wrap; gap: clamp(24px,5vw,80px)\">\n<div style=\"flex: 1 1 300px; display: flex; flex-direction: column; gap: 12px\"><span class=\"eb\" style=\"color: #7A5C2B\">Questions fr\u00e9quentes</span><h2 id=\"h-faq\" class=\"serif\" style=\"margin: 0; font-size: clamp(32px,4vw,52px); line-height: 1.05\">Tout savoir sur votre transfert au Bourget.</h2></div>\n<div style=\"flex: 2 1 480px; display: flex; flex-direction: column\"><sc-for list=\"{{faq}}\" as=\"f\" hint-placeholder-count=\"5\"><details style=\"border-top: 1px solid rgba(11,31,58,.12); padding: 18px 0\"><summary style=\"display: flex; justify-content: space-between; gap: 16px; font-size: 17px\"><span>{{f.q}}</span><span style=\"color: #7A5C2B\">+</span></summary><p style=\"margin: 12px 0 0; font-size: 15px; font-weight: 300; line-height: 1.65; color: rgba(11,31,58,.78)\">{{f.a}}</p></details></sc-for></div>\n</div>\n</section>\n\n<section aria-labelledby=\"h-oth\" style=\"padding: clamp(56px,8vw,110px) 0\">\n<div class=\"wrap\" style=\"display: flex; flex-direction: column; gap: 24px\">\n<div style=\"display: flex; flex-direction: column; gap: 12px\"><span class=\"eb\" style=\"color: #7A5C2B\">Autres transferts</span><h2 id=\"h-oth\" class=\"serif\" style=\"margin: 0; font-size: clamp(30px,3.4vw,44px); line-height: 1.05\">La Maison vous attend aussi ailleurs.</h2></div>\n<div class=\"grid g4\"><sc-for list=\"{{others}}\" as=\"o\" hint-placeholder-count=\"4\"><a href=\"#\" style=\"display: flex; flex-direction: column; gap: 8px; padding: 22px; border: 1px solid rgba(11,31,58,.14); border-radius: 4px; color: inherit; text-decoration: none; background: #FFFFFF\"><span class=\"serif\" style=\"font-size: 24px; line-height: 1.1\">{{o.n}}</span><span style=\"font-size: 13px; font-weight: 300; color: rgba(11,31,58,.7)\">{{o.d}}</span><span style=\"margin-top: 8px; font-size: 12px; letter-spacing: .16em; text-transform: uppercase; color: #7A5C2B\">{{o.p}} \u2192</span></a></sc-for></div>\n</div>\n</section>\n\n<section style=\"position: relative; color: #F6F1E7; overflow: hidden\">\n<img decoding=\"async\" src=\"https://cdn.jsdelivr.net/gh/LUD973/horizon@8de40a144ac46faa19a27cf075f6c9a983e734cc/site/home-v3/img/cta.jpg\" alt=\"\" style=\"position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover\">\n<div style=\"position: absolute; inset: 0; background: rgba(5,14,31,.72)\"></div>\n<div class=\"wrap\" style=\"position: relative; padding-top: clamp(64px,9vw,120px); padding-bottom: clamp(64px,9vw,120px); display: flex; flex-direction: column; align-items: center; text-align: center; gap: 18px\"><span class=\"eb\" style=\"color: #C9A867\">French Class Prestige</span><p class=\"serif\" style=\"margin: 0; font-size: clamp(32px,4.4vw,56px); line-height: 1.05\">Votre arriv\u00e9e \u00e0 Paris<br><em style=\"color: #C9A867\">commence ici.</em></p><div style=\"display: flex; flex-wrap: wrap; gap: 12px; justify-content: center\"><button type=\"button\" onClick=\"{{openBk}}\" class=\"btn\">R\u00e9server mon transfert</button><a href=\"https://wa.me/33656898611\" target=\"_blank\" rel=\"noopener\" class=\"btn ghost\" style=\"color: #F6F1E7\">\u00c9crire sur WhatsApp</a></div></div>\n</section>\n\n<footer style=\"background: #050E1F; color: rgba(246,241,231,.75); padding: 40px 0 120px; font-size: 13px\"><div class=\"wrap\" style=\"display: flex; flex-wrap: wrap; gap: 16px 40px; justify-content: space-between\"><span>French Class Prestige \u00b7 60 rue Fran\u00e7ois 1er, 75008 Paris</span><span>Mentions l\u00e9gales \u00b7 Conditions \u00b7 Confidentialit\u00e9</span></div></footer>\n\n<div class=\"mob\" style=\"position: fixed; left: 12px; right: 12px; bottom: 12px; z-index: 8; gap: 10px; padding: 8px; border-radius: 999px; background: rgba(5,14,31,.92); box-shadow: 0 12px 30px rgba(5,14,31,.35)\"><button type=\"button\" onClick=\"{{openBk}}\" class=\"btn\" style=\"flex: 1; border-radius: 999px; height: 48px\">R\u00e9server \u00b7 {{price}}</button><a href=\"https://wa.me/33656898611\" target=\"_blank\" rel=\"noopener\" aria-label=\"WhatsApp\" style=\"width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(201,168,103,.5); display: flex; align-items: center; justify-content: center; color: #C9A867\"><svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.5\"><path d=\"M20 11.6a8.4 8.4 0 0 1-12.4 7.4L4 20l1.1-3.5A8.4 8.4 0 1 1 20 11.6z\"/></svg></a></div>\n\n\n</div>\n",C:function(DCLogic,FCPV,FCP_API){
class Component extends DCLogic {
  constructor(p) { super(p); this.state = { dir: 'arr', tier: 'e', bk: false }; }
  renderVals() {
    const S = this.state;
    const P = { c: 110, e: 135, s: 220, v: 150 };
    const N = { c: 'Confort', e: 'Business', s: 'Signature', v: 'Van' };
    const fmt = n => n + ' €';
    const on = (a) => ({ bg: a ? '#0B1F3A' : '#FFFFFF', fg: a ? '#F6F1E7' : '#0B1F3A', border: a ? '#0B1F3A' : 'rgba(11,31,58,.18)', sel: a ? 'true' : 'false' });
    return {
      nav: ['Corporate', 'Private', 'Partner Hub', 'Destinations', 'Conciergerie', 'Notre Maison'],
      proofs: ['Suivi de vol en temps réel', 'Accueil nominatif', 'Chauffeur bilingue', 'Wi-Fi à bord', 'Tarif fixe, péages inclus'],
      dirs: [['arr', 'J’atterris au Bourget'], ['dep', 'Je pars du Bourget']].map(d => ({ l: d[1], pick: () => this.setState({ dir: d[0] }), ...on(S.dir === d[0]), bg: S.dir === d[0] ? '#0B1F3A' : 'transparent' })),
      addrLabel: S.dir === 'arr' ? 'Destination' : 'Prise en charge',
      dateLabel: S.dir === 'arr' ? 'Atterrissage' : 'Départ',
      tiers: ['c', 'e', 's', 'v'].map(k => ({ n: N[k], p: P[k], pick: () => this.setState({ tier: k }), ...on(S.tier === k) })),
      price: fmt(P[S.tier]), tierName: N[S.tier],
      steps: [
        { n: '01', t: 'Confirmation sous 30 minutes', d: 'Vous envoyez votre demande en une minute. La Maison confirme le tarif, le chauffeur et le véhicule par WhatsApp et e-mail.' },
        { n: '02', t: 'Votre vol est suivi', d: 'Avance, retard, changement de terminal : votre chauffeur s’adapte à l’heure réelle d’atterrissage. Vous n’avez rien à signaler.' },
        { n: '03', t: 'Accueil nominatif', d: 'Dès vos formalités terminées, votre chauffeur vous attend à la sortie du terminal de votre opérateur, une pancarte à votre nom.' },
        { n: '04', t: 'Jusqu’à votre porte', d: 'Bagages pris en charge par un chauffeur bilingue. À bord : rafraîchissements, chargeurs et Wi-Fi. Votre chauffeur choisit l’itinéraire le plus fluide du moment.' }
      ],
      cards: ['c', 'e', 's', 'v'].map(k => ({ n: N[k], p: fmt(P[k]), car: { c: 'Tesla ou Mercedes Classe C', e: 'Mercedes Classe E', s: 'Mercedes Classe S récente', v: 'Mercedes Classe V' }[k], cap: k === 'v' ? '7 passagers' : '3 passagers', bag: k === 'v' ? '7 bagages' : '2 soute + 2 cabine', border: S.tier === k ? '#C6A15B' : 'rgba(11,31,58,.1)' })),
      extras: ['Vol privé : indiquez l’immatriculation ou le numéro de vol', 'Groupes dès 8 passagers : tarif sur mesure', 'Siège enfant sur demande'],
      faq: [
        { q: 'Combien coûte un chauffeur entre Le Bourget et Paris ?', a: 'De 110 € en gamme Confort à 220 € en gamme Signature, par véhicule et toutes taxes comprises, entre l’aéroport de Paris-Le Bourget et Paris intra-muros. Le prix est fixe et connu avant le départ.' },
        { q: 'Mon vol est privé : que dois-je indiquer ?', a: 'Le nom de votre opérateur ou de votre terminal d’aviation d’affaires, et l’immatriculation de l’appareil ou le numéro de vol. Votre chauffeur s’adapte à l’heure réelle d’arrivée.' },
        { q: 'Où le chauffeur m’attend-il ?', a: 'À la sortie du terminal de votre opérateur d’aviation d’affaires, une pancarte à votre nom, dès vos formalités terminées.' },
        { q: 'Puis-je réserver pour un proche ou un collaborateur ?', a: 'Oui. Indiquez simplement le nom de la personne accueillie : elle recevra les informations de son chauffeur, et vous serez tenu informé de son arrivée.' },
        { q: 'Faut-il payer en ligne ?', a: 'Non. Vous envoyez une demande, la Maison la confirme, en général sous 30 minutes. Les modalités de règlement vous sont précisées à la confirmation.' }
      ],
      others: [
        { n: 'Paris-Charles de Gaulle', d: 'Roissy · terminaux 1, 2 et 3', p: 'Dès 110 €' },
        { n: 'Paris-Orly', d: 'Terminaux Orly 1 à 4', p: 'Dès 110 €' },
        { n: 'Paris-Beauvais', d: 'Beauvais-Tillé', p: 'Dès 190 €' },
        { n: 'Mise à disposition', d: 'À l’heure ou à la journée', p: 'Dès 225 €' }
      ],
      bk: S.bk,
      openBk: () => FCP_API.goBook({ route: 'airport', ap: 'lbg', tier: S.tier, dir: S.dir }),
      openGr: () => FCP_API.goBook({ route: 'airport', ap: 'lbg', tier: S.tier, dir: 'arr', greeter: true }),
      homeUrl: window.FCP_HOME_URL || '/',
      closeBk: () => this.setState({ bk: false })
    };
  }
}

return Component;}}};
var VALS = {}, TOKEN = null, MAP = { origin: 'qfrom', date: 'qdate', time: 'qtime' };
function FCPV(n) { var v = VALS[n]; return typeof v === 'string' ? v.trim() : v; }

function look(e, sc) { return e.trim().split('.').reduce(function (o, k) { return o == null ? undefined : o[k]; }, sc); }
function str(v) { return v == null || typeof v === 'function' ? '' : String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); }

function Instance(def, root) {
  var H = [], self = this, comp;
  function sub(s, sc) {
    s = s.replace(/ (on[A-Z]\w*)="\{\{([^}]+)\}\}"/g, function (_, ev, e) { var f = look(e, sc); if (typeof f !== 'function') return ''; H.push(f); return ' data-' + ev.slice(2).toLowerCase() + '="' + (H.length - 1) + '"'; });
    s = s.replace(/ ([\w-]+)="\{\{([^}]+)\}\}"/g, function (_, a, e) { var v = look(e, sc); if (v === false || v == null) return (a.indexOf('aria-') === 0 ? ' ' + a + '="false"' : ''); return ' ' + a + '="' + str(v) + '"'; });
    return s.replace(/\{\{([^}]+)\}\}/g, function (_, e) { return str(look(e, sc)); });
  }
  function render(s, sc) {
    var out = '';
    for (;;) {
      var i = s.search(/<sc-(for|if)\b/);
      if (i < 0) { out += sub(s, sc); break; }
      out += sub(s.slice(0, i), sc);
      var tag = s.slice(i + 1).match(/^sc-(for|if)/)[0], oe = s.indexOf('>', i), open = s.slice(i, oe + 1), d = 1;
      var re = new RegExp('<' + tag + '\\b|</' + tag + '>', 'g'), mm; re.lastIndex = oe + 1;
      while ((mm = re.exec(s))) { d += mm[0][1] === '/' ? -1 : 1; if (!d) break; }
      var inner = s.slice(oe + 1, mm.index); s = s.slice(mm.index + tag.length + 3);
      var val = look(open.match(/(?:list|value)="\{\{([^}]+)\}\}"/)[1], sc);
      if (tag === 'sc-if') { if (val) out += render(inner, sc); }
      else { var as = open.match(/as="(\w+)"/)[1]; (val || []).forEach(function (it) { var o = {}; for (var k in sc) o[k] = sc[k]; o[as] = it; out += render(inner, o); }); }
    }
    return out;
  }
  function capture() {
    root.querySelectorAll('[name]').forEach(function (el) { VALS[el.name] = el.type === 'checkbox' ? el.checked : el.value; });
  }
  function restore() {
    root.querySelectorAll('[name]').forEach(function (el) {
      var v = VALS[el.name];
      if ((v == null || v === '') && MAP[el.name] && VALS[MAP[el.name]]) v = VALS[el.name] = VALS[MAP[el.name]];
      if (v == null) return;
      if (el.type === 'checkbox') el.checked = !!v; else if (el.value !== v && el !== document.activeElement) el.value = v;
    });
    root.querySelectorAll('select[value]').forEach(function (el) { el.value = el.getAttribute('value'); });
  }
  function syncAttrs(x, y) {
    var i, a;
    for (i = x.attributes.length - 1; i >= 0; i--) { a = x.attributes[i].name; if (!y.hasAttribute(a)) x.removeAttribute(a); }
    for (i = 0; i < y.attributes.length; i++) { a = y.attributes[i]; if (x.getAttribute(a.name) !== a.value) x.setAttribute(a.name, a.value); }
  }
  function morphKids(a, b) {
    var bk = [].slice.call(b.childNodes), ak = [].slice.call(a.childNodes), i;
    for (i = 0; i < bk.length; i++) {
      var x = ak[i], y = bk[i];
      if (!x) { a.appendChild(y); continue; }
      if (x.nodeType !== y.nodeType || x.nodeName !== y.nodeName) { a.replaceChild(y, x); continue; }
      if (x.nodeType !== 1) { if (x.nodeValue !== y.nodeValue) x.nodeValue = y.nodeValue; continue; }
      syncAttrs(x, y);
      if (x.nodeName === 'TEXTAREA') continue;
      morphKids(x, y);
    }
    for (i = ak.length - 1; i >= bk.length; i--) a.removeChild(ak[i]);
  }
  function paint() {
    capture();
    H = [];
    var html = render(def.tpl, comp.renderVals());
    if (!root.firstChild) root.innerHTML = html;
    else { var tmp = document.createElement('div'); tmp.innerHTML = html; morphKids(root, tmp); }
    restore();
  }
  var Base = function (p) { this.props = p || {}; this.state = {}; };
  Base.prototype.setState = function (p) { var n = typeof p === 'function' ? p(this.state) : p; for (var k in n) this.state[k] = n[k]; paint(); };
  var Cls = def.C(Base, FCPV, API);
  this.onClick = function (ev) {
    var lb = ev.target.closest('button');
    if (lb && !lb.hasAttribute('data-click') && /^(FR|EN|PT|ES)$/.test(lb.textContent.trim())) {
      var lang = lb.textContent.trim().toLowerCase();
      if (typeof window.doGTranslate === 'function') window.doGTranslate('fr|' + lang);
      [].forEach.call(lb.parentNode.querySelectorAll('button'), function (x) { x.setAttribute('aria-pressed', x === lb ? 'true' : 'false'); });
      return;
    }
    var t = ev.target.closest('[data-click]'); if (t && root.contains(t)) { if (t.tagName !== 'A' || t.getAttribute('href') === '#' || !t.getAttribute('href')) ev.preventDefault(); H[+t.dataset.click](ev); } };
  this.onChange = function (ev) { var nm = ev.target.name; if (nm) VALS[nm] = ev.target.type === 'checkbox' ? ev.target.checked : ev.target.value; var t = ev.target.closest('[data-change]'); if (t) H[+t.dataset.change](ev); if (nm === 'origin' || nm === 'destination' || nm === 'qfrom') setTimeout(function () { API.detectRoute(comp); comp.setState({ addrT: Date.now() }); }, 0); if (nm === 'origin' || nm === 'destination' || nm === 'date' || nm === 'flight') API.scheduleFlightCheck(comp); };
  this.onInput = function (ev) { if (ev.target.name) { VALS[ev.target.name] = ev.target.value; if (ev.isTrusted) VALS[ev.target.name + '_ll'] = null; } if (ev.target.name === 'flight' || ev.target.name === 'ret_flight') { comp.setState({ flT: Date.now() }); if (ev.target.name === 'flight') API.scheduleFlightCheck(comp); } };
  this.onScroll = function (ev) { var t = ev.target; if (t && t.dataset && t.dataset.scroll) H[+t.dataset.scroll]({ currentTarget: t, target: t }); };
  root.addEventListener('click', this.onClick);
  root.addEventListener('change', this.onChange);
  root.addEventListener('input', this.onInput);
  root.addEventListener('scroll', this.onScroll, true);
  comp = new Cls({});
  this.comp = comp; kmComp = comp;
  paint();
  if (comp.componentDidMount) comp.componentDidMount();
  this.destroy = function () {
    capture();
    if (comp.componentWillUnmount) comp.componentWillUnmount();
    if (comp.tour) clearInterval(comp.tour);
    root.removeEventListener('click', self.onClick); root.removeEventListener('change', self.onChange);
    root.removeEventListener('input', self.onInput); root.removeEventListener('scroll', self.onScroll, true);
    comp.setState = function () {};
    root.innerHTML = '';
  };
}

/* ---------- Enquiry API (FCP Horizon Bridge) ---------- */
var API_ROOT = '/wp-json/fcp/v1/';
function uuid() { return (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : 'fcp-' + Date.now() + '-' + Math.random().toString(16).slice(2); }
function getToken() {
  return fetch(API_ROOT + 'form-token?_=' + Date.now(), { credentials: 'same-origin', cache: 'no-store' }).then(function (r) { return r.json(); }).then(function (j) { TOKEN = j.token; return TOKEN; });
}
function today() { var d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); }
var API = {
  sendBooking: function (comp) {
    var S = comp.state;
    if (S.bkBusy) return;
    var r = comp.renderVals(), k = r.__bk || {}, err = [];
    var name = (FCPV('full_name') || '').split(/\s+/).filter(Boolean);
    if (name.length < 2) err.push('Indiquez votre prénom et votre nom.');
    var email = FCPV('email') || '';
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) err.push('Indiquez une adresse e-mail valide.');
    var phone = FCPV('phone') || '';
    if (phone.replace(/[^0-9]/g, '').length < 8) err.push('Indiquez un numéro de téléphone joignable.');
    var date = FCPV('date') || FCPV('qdate') || '';
    if (!date) err.push('Choisissez la date de prise en charge.');
    else if (date < today()) err.push('La date doit être aujourd’hui ou ultérieure.');
    if (!FCPV('consent')) err.push('Merci d’accepter le traitement de votre demande.');
    if (err.length) { comp.setState({ bkErr: err.join(' ') }); return; }
    var routeLabel = ((r.recap || [])[1] || {}).v || '';
    var origin = FCPV('origin') || FCPV('qfrom') || (k.isTransfer ? routeLabel : '');
    var dest = FCPV('destination') || (k.isTransfer ? routeLabel : S.bMode === 'tour' ? 'City tour · ' + API.offerLabel(S) : S.bMode === 'excursion' ? 'Excursion · ' + API.offerLabel(S) : 'Mise à disposition' + (S.bDur ? ' · ' + S.bDur : ''));
    if (!origin || origin.length < 2) { comp.setState({ bkErr: 'Indiquez le lieu de prise en charge.' }); return; }
    var pax = k.bigGroup ? k.gPax : ({ '1 – 3': 3, '4 – 7': 7 })[S.bPax] || 1;
    var lines = (r.recap || []).map(function (x) { return x.k + ' : ' + x.v; });
    lines.push('Total indicatif : ' + (r.totalLabel || 'Tarif sur mesure'));
    if (API.kmInfo()) lines.push('Distance estimée : ' + API.kmInfo() + ' (tarif au kilomètre)');
    if (!k.isTransfer) lines.push('Règle : ' + KM_PER_H + ' km inclus par heure, kilomètre supplémentaire au tarif de la gamme');
    var fl = API.flight(FCPV('flight'));
    if (fl.code) lines.push('Vol : ' + fl.code + (fl.al ? ' (' + fl.al + ')' : ' (compagnie non reconnue)') + ' · Suivi : ' + fl.url);
    var rf = API.flight(FCPV('ret_flight'));
    if (rf.code) lines.push('Vol retour : ' + rf.code + (rf.al ? ' (' + rf.al + ')' : '') + ' · Suivi : ' + rf.url);
    if (FCPV('pax_name')) lines.push('Passager : ' + FCPV('pax_name'));
    if (k.retM) lines.push('Retour : ' + [FCPV('ret_date'), FCPV('ret_time'), FCPV('ret_flight')].filter(Boolean).join(' · '));
    var payload = {
      _fcp_nonce: '', category: 'mobility', subcategory: k.isTransfer ? 'transfer' : ({ tour: 'city_tour', excursion: 'excursion' })[S.bMode] || 'hourly', locale: 'fr', profile: 'individual',
      first_name: name[0], last_name: name.slice(1).join(' '), email: email, phone: phone,
      service_date: date, service_time: FCPV('time') || FCPV('qtime') || '', origin: origin.slice(0, 160), destination: dest.slice(0, 160),
      passengers: pax, luggage: (k.bagS || 0) + (k.bagC || 0), flight_number: (fl.code || FCPV('flight') || '').replace(' ', ''), train_number: '',
      range: k.tierEff || '', preferred_channel: 'whatsapp', message: lines.join('  •  ').slice(0, 1500),
      consent_processing: '1', consent_marketing: '', company_website: ''
    };
    var ft = flightTarget();
    if (ft && fl.code) { payload.flight_airport = ft.ap; payload.flight_dir = ft.dir; }
    comp.setState({ bkBusy: true, bkErr: '' });
    var key = S.bkKey || uuid(); comp.state.bkKey = key;
    checkFlight(null).then(function (c) {
      if (c && (c.status === 'mismatch' || c.status === 'not_found')) { var e = new Error(c.message); e.flight = true; throw e; }
      return getToken();
    }).then(function (t) {
      payload._fcp_nonce = t;
      return fetch(API_ROOT + 'enquiries', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Idempotency-Key': key }, body: JSON.stringify(payload) });
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, status: res.status, j: j }; }); })
      .then(function (o) {
        if (o.ok && o.j && o.j.success) {
          comp.setState({ bkBusy: false, bkKey: null, bStep: 'done', requested: true, bkRef: o.j.reference || null, bkWa: o.j.whatsapp_url || WA });
        } else if (o.status === 422 && o.j && o.j.errors) {
          var m = []; for (var f in o.j.errors) m.push(o.j.errors[f]);
          comp.setState({ bkBusy: false, bkErr: m.join(' ') });
        } else {
          comp.setState({ bkBusy: false, bkErr: (o.j && o.j.message) || 'L’envoi n’a pas abouti. Réessayez, ou écrivez-nous sur WhatsApp.' });
        }
      }).catch(function (e) { comp.setState({ bkBusy: false, bkErr: e && e.flight ? e.message : 'Connexion interrompue. Réessayez, ou écrivez-nous sur WhatsApp.' }); });
  },
  waOpened: function (ref) {
    if (!ref) return;
    var send = function (t) { return fetch(API_ROOT + 'enquiries/' + encodeURIComponent(ref) + '/whatsapp-opened', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ _fcp_nonce: t }), keepalive: true }); };
    (TOKEN ? Promise.resolve(TOKEN) : getToken()).then(send).catch(function () {});
  },
  sendProject: function (comp) {
    var r = comp.renderVals(), pj = r.pj || {}, S = comp.state, out = ['Bonjour French Class Prestige,', ''];
    out.push((pj.title || 'Ma demande') + (pj.label ? ' · ' + pj.label : ''));
    if (S.pNeed) out.push((pj.needLabel || 'Besoin') + ' : ' + S.pNeed);
    (r.pFields || []).forEach(function (f) { var v = FCPV(f.name); if (v) out.push(f.label + ' : ' + v); });
    if (FCPV('pj_msg')) { out.push(''); out.push(FCPV('pj_msg')); }
    window.open(WA + '?text=' + encodeURIComponent(out.join('\n')), '_blank', 'noopener');
  }
};

/* ---------- Address suggestions (Géoplateforme IGN + lieux de la Maison) ---------- */
var GEO = 'https://data.geopf.fr/geocodage/';
var PLACES = [
  ['Aéroport Paris-Charles de Gaulle (CDG)', 'Roissy · terminaux 1, 2 et 3', 'cdg roissy charles de gaulle aeroport airport t1 t2 t3 2a 2b 2c 2d 2e 2f 2g'],
  ['Aéroport Paris-Orly (ORY)', 'Orly · terminaux 1 à 4', 'orly aeroport airport ory'],
  ['Aéroport Paris-Le Bourget (LBG)', 'Aviation d’affaires · Le Bourget', 'bourget aeroport aviation affaires jet prive lbg'],
  ['Aéroport Paris-Beauvais (BVA)', 'Beauvais-Tillé', 'beauvais aeroport bva tille'],
  ['Gare du Nord', 'Paris 10e · Eurostar, Thalys', 'gare du nord eurostar thalys'],
  ['Gare de Lyon', 'Paris 12e · TGV Sud-Est', 'gare de lyon tgv'],
  ['Gare Montparnasse', 'Paris 15e · TGV Atlantique', 'gare montparnasse tgv'],
  ['Gare de l’Est', 'Paris 10e · TGV Est', 'gare de l est tgv'],
  ['Gare Saint-Lazare', 'Paris 8e', 'gare saint lazare st'],
  ['Gare d’Austerlitz', 'Paris 13e', 'gare d austerlitz'],
  ['Gare de Marne-la-Vallée – Chessy', 'Disneyland Paris', 'marne la vallee chessy gare disney disneyland'],
  ['Disneyland Paris', 'Chessy · Parcs et hôtels', 'disneyland disney parc chessy'],
  ['Château de Versailles', 'Versailles', 'chateau de versailles palais'],
  ['Hôtel Plaza Athénée', '25 avenue Montaigne, Paris 8e', 'hotel plaza athenee palace montaigne'],
  ['Four Seasons Hotel George V', '31 avenue George V, Paris 8e', 'hotel four seasons george v palace'],
  ['Le Bristol Paris', '112 rue du Faubourg Saint-Honoré, Paris 8e', 'hotel le bristol palace faubourg saint honore'],
  ['Hôtel de Crillon', '10 place de la Concorde, Paris 8e', 'hotel crillon rosewood palace concorde'],
  ['Ritz Paris', '15 place Vendôme, Paris 1er', 'hotel ritz palace vendome'],
  ['Le Meurice', '228 rue de Rivoli, Paris 1er', 'hotel le meurice palace rivoli'],
  ['The Peninsula Paris', '19 avenue Kléber, Paris 16e', 'hotel peninsula palace kleber'],
  ['Shangri-La Paris', '10 avenue d’Iéna, Paris 16e', 'hotel shangri la palace iena'],
  ['Mandarin Oriental Paris', '251 rue Saint-Honoré, Paris 1er', 'hotel mandarin oriental palace saint honore'],
  ['Cheval Blanc Paris', '8 quai du Louvre, Paris 1er', 'hotel cheval blanc palace samaritaine louvre'],
  ['Le Royal Monceau', '37 avenue Hoche, Paris 8e', 'hotel royal monceau raffles hoche'],
  ['Park Hyatt Paris-Vendôme', '5 rue de la Paix, Paris 2e', 'hotel park hyatt vendome paix'],
  ['Hôtel Lutetia', '45 boulevard Raspail, Paris 6e', 'hotel lutetia raspail'],
  ['Hôtel Costes', '239 rue Saint-Honoré, Paris 1er', 'hotel costes saint honore'],
  ['Bulgari Hotel Paris', '30 avenue George V, Paris 8e', 'hotel bulgari bvlgari george v'],
  ['Prince de Galles', '33 avenue George V, Paris 8e', 'hotel prince de galles george v'],
  ['Hôtel Barrière Le Fouquet’s', '46 avenue George V, Paris 8e', 'hotel barriere fouquet fouquets george v'],
  ['La Réserve Paris', '42 avenue Gabriel, Paris 8e', 'hotel la reserve gabriel'],
  ['Hôtel Lancaster', '7 rue de Berri, Paris 8e', 'hotel lancaster berri'],
  ['Sofitel Paris Le Faubourg', '15 rue Boissy d’Anglas, Paris 8e', 'hotel sofitel faubourg boissy anglas'],
  ['Hôtel Napoléon', '40 avenue de Friedland, Paris 8e', 'hotel napoleon friedland'],
  ['Paris Marriott Champs-Élysées', '70 avenue des Champs-Élysées, Paris 8e', 'hotel marriott champs elysees'],
  ['Hôtel Raphael', '17 avenue Kléber, Paris 16e', 'hotel raphael kleber'],
  ['Saint James Paris', '5 place du Chancelier Adenauer, Paris 16e', 'hotel saint james st adenauer'],
  ['Hôtel Molitor', '13 rue Nungesser et Coli, Paris 16e', 'hotel molitor mgallery'],
  ['Hyatt Regency Paris Étoile', '3 place du Général Kœnig, Paris 17e', 'hotel hyatt regency etoile porte maillot koenig'],
  ['InterContinental Paris Le Grand', '2 rue Scribe, Paris 9e', 'hotel intercontinental le grand scribe opera'],
  ['Kimpton St Honoré Paris', '27 boulevard des Capucines, Paris 2e', 'hotel kimpton st saint honore capucines'],
  ['Hôtel Westminster', '13 rue de la Paix, Paris 2e', 'hotel westminster paix'],
  ['Le Burgundy Paris', '6 rue Duphot, Paris 1er', 'hotel le burgundy duphot'],
  ['Hôtel Regina Louvre', '2 place des Pyramides, Paris 1er', 'hotel regina louvre pyramides'],
  ['Hôtel du Louvre', 'Place André Malraux, Paris 1er', 'hotel du louvre malraux'],
  ['Madame Rêve', '48 rue du Louvre, Paris 1er', 'hotel madame reve louvre'],
  ['Le Pavillon de la Reine', '28 place des Vosges, Paris 3e', 'hotel pavillon de la reine vosges marais'],
  ['Pullman Paris Tour Eiffel', '18 avenue de Suffren, Paris 15e', 'hotel pullman tour eiffel suffren'],
  ['Waldorf Astoria Versailles – Trianon Palace', '1 boulevard de la Reine, Versailles', 'hotel waldorf astoria trianon palace versailles'],
  ['Airelles Château de Versailles', 'Le Grand Contrôle, Versailles', 'hotel airelles grand controle chateau versailles'],
  ['Palais des Congrès de Paris', 'Porte Maillot, Paris 17e', 'palais des congres porte maillot'],
  ['Paris Expo Porte de Versailles', 'Paris 15e', 'paris expo porte de versailles salon'],
  ['Paris Nord Villepinte', 'Villepinte · Parc des expositions', 'villepinte parc des expositions salon']
];
var PLACE_LL = {
  'Aéroport Paris-Charles de Gaulle (CDG)': [49.0097, 2.5479], 'Aéroport Paris-Orly (ORY)': [48.7262, 2.3652],
  'Aéroport Paris-Le Bourget (LBG)': [48.9694, 2.4414], 'Aéroport Paris-Beauvais (BVA)': [49.4544, 2.1128],
  'Gare du Nord': [48.8809, 2.3553], 'Gare de Lyon': [48.8443, 2.3744], 'Gare Montparnasse': [48.8412, 2.3203],
  'Gare de l’Est': [48.8768, 2.3592], 'Gare Saint-Lazare': [48.8763, 2.3254], 'Gare d’Austerlitz': [48.8423, 2.3655],
  'Gare de Marne-la-Vallée – Chessy': [48.8700, 2.7830], 'Disneyland Paris': [48.8722, 2.7758], 'Château de Versailles': [48.8049, 2.1204],
  'Waldorf Astoria Versailles – Trianon Palace': [48.8090, 2.1188], 'Airelles Château de Versailles': [48.8035, 2.1235],
  'Palais des Congrès de Paris': [48.8786, 2.2834], 'Paris Expo Porte de Versailles': [48.8320, 2.2875], 'Paris Nord Villepinte': [48.9705, 2.5150]
};
var AC_FIELDS = { origin: 1, destination: 1, qfrom: 1, addr: 1 };
var acBox = null, acInput = null, acItems = [], acSel = -1, acTimer = null, acSeq = 0;
function norm(t) { return String(t || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim(); }
function acEnsure() {
  if (acBox) return acBox;
  acBox = document.createElement('div');
  acBox.id = 'fcp-ac';
  acBox.setAttribute('role', 'listbox');
  document.body.appendChild(acBox);
  acBox.addEventListener('pointerdown', function (e) { e.preventDefault(); });
  acBox.addEventListener('mousedown', function (e) { e.preventDefault(); });
  acBox.addEventListener('click', function (e) { var r = e.target.closest('[data-i]'); if (r) acPick(+r.getAttribute('data-i')); });
  return acBox;
}
function acHide() { if (acBox) { acBox.style.display = 'none'; acBox.innerHTML = ''; } acItems = []; acSel = -1; }
function acPlace() {
  if (!acBox || !acInput || acBox.style.display === 'none') return;
  if (!document.contains(acInput)) { acHide(); return; }
  var r = acInput.getBoundingClientRect(), vv = window.visualViewport, vh = vv ? vv.height + vv.offsetTop : window.innerHeight;
  if (r.bottom < 0 || r.top > vh) { acBox.style.visibility = 'hidden'; return; }
  acBox.style.visibility = 'visible';
  var w = Math.max(r.width, 280), left = Math.min(r.left, window.innerWidth - w - 8);
  var below = vh - r.bottom - 12, above = r.top - 12;
  acBox.style.left = Math.max(8, left) + 'px';
  acBox.style.width = Math.min(w, window.innerWidth - 16) + 'px';
  if (below >= 180 || below >= above) { acBox.style.top = (r.bottom + 6) + 'px'; acBox.style.bottom = 'auto'; acBox.style.maxHeight = Math.max(140, Math.min(360, below)) + 'px'; }
  else { acBox.style.top = 'auto'; acBox.style.bottom = (vh - r.top + 6) + 'px'; acBox.style.maxHeight = Math.min(360, above) + 'px'; }
}
function acRender(head) {
  var box = acEnsure();
  if (!acItems.length) { acHide(); return; }
  var h = '', lastG = null;
  acItems.forEach(function (it, i) {
    if (it.g !== lastG) { h += '<div class="g">' + it.g + '</div>'; lastG = it.g; }
    h += '<div class="o" role="option" data-i="' + i + '" aria-selected="' + (i === acSel) + '"><span class="ic">' + (it.ic || '') + '</span><span class="t"><b>' + str(it.t) + '</b><small>' + str(it.s || '') + '</small></span></div>';
  });
  box.innerHTML = h;
  box.style.display = 'block';
  acPlace();
}
var IC_PIN = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B08D4F" stroke-width="1.5"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>';
var IC_STAR = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B08D4F" stroke-width="1.5"><path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6 6.6 19.5l1.2-6L3.3 9.3l6.1-.7z"/></svg>';
function acQuery(q) {
  var nq = norm(q), seq = ++acSeq, local = [];
  var words = nq.split(' ').filter(Boolean);
  PLACES.forEach(function (p) {
    var hay = norm(p[0] + ' ' + p[1] + ' ' + p[2]);
    if (!words.length || words.every(function (w) { return hay.indexOf(w) >= 0; })) local.push({ g: 'Lieux de la Maison', t: p[0], s: p[1], v: p[0] + (p[1] && p[1].indexOf('Paris') >= 0 && /\d/.test(p[1]) ? ' — ' + p[1] : ''), ic: IC_STAR, ll: PLACE_LL[p[0]] || (/Paris \d/.test(p[1]) ? [48.8566, 2.3522] : null) });
  });
  local = local.slice(0, words.length ? 4 : 6);
  acItems = local; acSel = -1; acRender();
  if (nq.length < 3) return;
  var places = [], addrs = [], done = 0;
  function merge() {
    if (seq !== acSeq || !acInput) return;
    var seen = {}, out = [];
    local.slice(0, 3).concat(places, addrs).forEach(function (it) { var k = norm(it.t); if (!seen[k]) { seen[k] = 1; out.push(it); } });
    acItems = out.slice(0, 9); acSel = -1; acRender();
  }
  // Hotels, restaurants, venues, landmarks — OpenStreetMap (Photon), worldwide, biased to Paris
  fetch('https://photon.komoot.io/api/?q=' + encodeURIComponent(q) + '&limit=6&lang=fr&lat=48.8566&lon=2.3522', { mode: 'cors' })
    .then(function (r) { return r.json(); })
    .then(function (j) {
      places = (j.features || []).filter(function (f) { var p = f.properties || {}; return p.name && p.osm_key !== 'highway' && p.osm_key !== 'place' && p.osm_key !== 'boundary'; }).slice(0, 4).map(function (f) {
        var p = f.properties || {};
        var street = [p.housenumber, p.street].filter(Boolean).join(' ');
        var city = [p.postcode, p.city || p.town || p.village || p.county].filter(Boolean).join(' ');
        var sub = [street, city, p.country && p.country !== 'France' ? p.country : ''].filter(Boolean).join(', ');
        var gc = (f.geometry || {}).coordinates; return { g: 'Hôtels et lieux', t: p.name, s: sub, v: p.name + (sub ? ' — ' + sub : ''), ic: IC_STAR, ll: gc ? [gc[1], gc[0]] : null };
      });
      merge();
    }).catch(function () {});
  // Street addresses — Géoplateforme IGN (France)
  fetch(GEO + 'search?q=' + encodeURIComponent(q) + '&limit=5&autocomplete=1&lat=48.8566&lon=2.3522', { mode: 'cors' })
    .then(function (r) { return r.json(); })
    .then(function (j) {
      addrs = (j.features || []).map(function (f) {
        var p = f.properties || {};
        var t = p.name || p.label, s2 = [p.postcode, p.city].filter(Boolean).join(' ');
        if (p.type === 'municipality') s2 = p.context || s2;
        var gc = (f.geometry || {}).coordinates; return { g: 'Adresses', t: t, s: s2, v: p.label || t, ic: IC_PIN, ll: gc ? [gc[1], gc[0]] : null };
      });
      merge();
    }).catch(function () {});
}
function acPick(i) {
  var it = acItems[i]; if (!it || !acInput) return;
  acInput.value = it.v;
  VALS[acInput.name] = it.v;
  VALS[acInput.name + '_ll'] = it.ll || null;
  acInput.dispatchEvent(new Event('input', { bubbles: true }));
  acInput.dispatchEvent(new Event('change', { bubbles: true }));
  acHide();
  if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) acInput.blur();
}
function acInit() {
  var st = document.createElement('style');
  st.textContent = '#fcp-ac{position:fixed;z-index:100002;display:none;overflow-y:auto;-webkit-overflow-scrolling:touch;background:#FBF8F2;border:1px solid rgba(176,141,79,.35);border-radius:12px;box-shadow:0 18px 40px rgba(5,14,31,.28);font-family:Jost,system-ui,sans-serif;padding:6px 0;box-sizing:border-box}'
    + '#fcp-ac .g{font-size:10px;letter-spacing:2.2px;text-transform:uppercase;color:#B08D4F;padding:10px 16px 4px}'
    + '#fcp-ac .o{display:flex;align-items:flex-start;gap:12px;padding:10px 16px;cursor:pointer;color:#0B1F3A}'
    + '#fcp-ac .o[aria-selected=true],#fcp-ac .o:hover{background:rgba(198,161,91,.14)}'
    + '#fcp-ac .ic{flex:0 0 16px;padding-top:2px}#fcp-ac .t{display:flex;flex-direction:column;gap:2px;min-width:0}'
    + '#fcp-ac b{font-weight:400;font-size:15px;line-height:1.3}#fcp-ac small{font-size:12px;font-weight:300;color:rgba(11,31,58,.62);line-height:1.3}';
  document.head.appendChild(st);
  function field(t) { return t && t.name && AC_FIELDS[t.name] && t.closest && t.closest('#fcp-home') ? t : null; }
  document.addEventListener('focusin', function (e) {
    var t = field(e.target); if (!t) return;
    acInput = t; t.setAttribute('autocomplete', 'off');
    acQuery(t.value || '');
  });
  document.addEventListener('input', function (e) {
    var t = field(e.target); if (!t || !e.isTrusted) return;
    acInput = t; clearTimeout(acTimer);
    var v = t.value; acTimer = setTimeout(function () { acQuery(v); }, 180);
  });
  document.addEventListener('focusout', function (e) { if (field(e.target)) setTimeout(function () { if (document.activeElement !== acInput) acHide(); }, 120); });
  document.addEventListener('keydown', function (e) {
    if (!acInput || e.target !== acInput || !acItems.length) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') { e.preventDefault(); acSel = (acSel + (e.key === 'ArrowDown' ? 1 : -1) + acItems.length) % acItems.length; acRender(); }
    else if (e.key === 'Enter' && acSel >= 0) { e.preventDefault(); acPick(acSel); }
    else if (e.key === 'Escape') acHide();
  });
  window.addEventListener('scroll', acPlace, true);
  window.addEventListener('resize', acPlace);
  if (window.visualViewport) { window.visualViewport.addEventListener('resize', acPlace); window.visualViewport.addEventListener('scroll', acPlace); }
}

function setField(n, v) { VALS[n] = v; var el = document.querySelector('#fcp-home [name="' + n + '"]'); if (el) el.value = v; }
API.locate = function (comp) {
  if (!navigator.geolocation) { comp.setState({ loc: 'err' }); return; }
  comp.setState({ loc: 'busy' });
  navigator.geolocation.getCurrentPosition(function (pos) {
    var c = pos.coords;
    fetch(GEO + 'reverse?lon=' + c.longitude + '&lat=' + c.latitude + '&limit=1', { mode: 'cors' }).then(function (r) { return r.json(); }).then(function (j) {
      var p = ((j.features || [])[0] || {}).properties;
      if (!p) throw 0;
      setField('origin', p.label); VALS.origin_ll = [c.latitude, c.longitude];
      comp.setState({ loc: true, locShort: [p.postcode, p.city].filter(Boolean).join(' ') });
      API.detectRoute(comp);
    }).catch(function () {
      setField('origin', 'Position GPS : ' + c.latitude.toFixed(5) + ', ' + c.longitude.toFixed(5));
      comp.setState({ loc: true, locShort: '' });
    });
  }, function (e) {
    comp.setState({ loc: e && e.code === 1 ? 'denied' : 'err' });
    var el = document.querySelector('#fcp-home [name="origin"]'); if (el) { try { el.focus(); } catch (x) {} }
  }, { enableHighAccuracy: false, timeout: 15000, maximumAge: 300000 });
};

/* ---------- Flight check ---------- */
var AIRLINES = {AF:'Air France',KL:'KLM',LH:'Lufthansa',BA:'British Airways',U2:'easyJet',EC:'easyJet Europe',DS:'easyJet Suisse',FR:'Ryanair',VY:'Vueling',IB:'Iberia',UX:'Air Europa',AZ:'ITA Airways',LX:'Swiss',OS:'Austrian Airlines',SN:'Brussels Airlines',TP:'TAP Air Portugal',SK:'SAS',AY:'Finnair',EK:'Emirates',QR:'Qatar Airways',EY:'Etihad Airways',TK:'Turkish Airlines',PC:'Pegasus',XQ:'SunExpress',DL:'Delta Air Lines',AA:'American Airlines',UA:'United Airlines',AC:'Air Canada',TS:'Air Transat',WS:'WestJet',B6:'JetBlue',LA:'LATAM',AV:'Avianca',AM:'Aeroméxico',CM:'Copa Airlines',G3:'Gol',AD:'Azul',JL:'Japan Airlines',NH:'ANA',CX:'Cathay Pacific',SQ:'Singapore Airlines',KE:'Korean Air',OZ:'Asiana Airlines',CA:'Air China',MU:'China Eastern',CZ:'China Southern',HU:'Hainan Airlines',AI:'Air India','6E':'IndiGo',SV:'Saudia',MS:'EgyptAir',AT:'Royal Air Maroc','3O':'Air Arabia Maroc',TU:'Tunisair',BJ:'Nouvelair',AH:'Air Algérie',ET:'Ethiopian Airlines',KQ:'Kenya Airways',SA:'South African Airways',TO:'Transavia France',HV:'Transavia',DY:'Norwegian',D8:'Norwegian',W6:'Wizz Air',W4:'Wizz Air Malta',LO:'LOT Polish Airlines',A3:'Aegean Airlines',EI:'Aer Lingus',BT:'airBaltic',SS:'Corsair',TX:'Air Caraïbes',TN:'Air Tahiti Nui',UU:'Air Austral',BF:'French bee',XK:'Air Corsica',V7:'Volotea',QF:'Qantas',NZ:'Air New Zealand',VS:'Virgin Atlantic',LG:'Luxair',WY:'Oman Air',GF:'Gulf Air',KU:'Kuwait Airways',RJ:'Royal Jordanian',ME:'Middle East Airlines',LY:'El Al',VN:'Vietnam Airlines',TG:'Thai Airways',MH:'Malaysia Airlines',GA:'Garuda Indonesia',PR:'Philippine Airlines',OU:'Croatia Airlines',JU:'Air Serbia',RO:'Tarom',FB:'Bulgaria Air',DE:'Condor',EW:'Eurowings','4U':'Eurowings',KC:'Air Astana',HY:'Uzbekistan Airways',PS:'Ukraine International',J2:'Azerbaijan Airlines',ZI:'Aigle Azur',A5:'HOP!',YO:'Heli Air Monaco',BE:'Flybe',LS:'Jet2',MT:'Thomas Cook',BY:'TUI Airways',TB:'TUI fly',X3:'TUI fly',OR:'TUI fly Nederland',HF:'Air Côte d’Ivoire',KP:'ASKY',DT:'TAAG Angola',QC:'Camair-Co',SM:'Air Cairo',VT:'Air Tahiti',NF:'Air Vanuatu',SB:'Aircalin',OG:'Air Guilin',FI:'Icelandair',OK:'Czech Airlines',QS:'Smartwings',H4:'HiSky',FZ:'flydubai',G9:'Air Arabia',XY:'flynas',F3:'flyadeal',PK:'PIA',UL:'SriLankan Airlines',BG:'Biman',KM:'KM Malta Airlines',KX:'Cayman Airways',BW:'Caribbean Airlines',HA:'Hawaiian Airlines',AS:'Alaska Airlines',WN:'Southwest',NK:'Spirit',F9:'Frontier'};
var FLC = { key: '', status: '', message: '', leg: null }, flTimer = null;
var AP_IATA = { cdg: 'CDG', orly: 'ORY', lbg: 'LBG', bva: 'BVA' };
function flightTarget() {
  var ka = kindOf(FCPV('origin') || FCPV('qfrom') || ''), kb = kindOf(FCPV('destination') || '');
  if (AP_IATA[ka]) return { ap: AP_IATA[ka], dir: 'arr' };
  if (AP_IATA[kb]) return { ap: AP_IATA[kb], dir: 'dep' };
  return null;
}
function flightKey() {
  var f = String(FCPV('flight') || '').toUpperCase().replace(/[^A-Z0-9]/g, ''), d = FCPV('date') || FCPV('qdate') || '', t = flightTarget();
  if (!f || !d || !t || !/^([A-Z0-9]{2})(\d{1,4})[A-Z]?$/.test(f)) return null;
  return { k: f + '|' + d + '|' + t.ap + '|' + t.dir, f: f, d: d, ap: t.ap, dir: t.dir };
}
function checkFlight(comp) {
  var q = flightKey();
  if (!q) { FLC = { key: '', status: '' }; return Promise.resolve(FLC); }
  if (FLC.key === q.k && FLC.status && FLC.status !== 'pending') return Promise.resolve(FLC);
  FLC = { key: q.k, status: 'pending', message: '', leg: null };
  if (comp) comp.setState({ flT: Date.now() });
  return fetch(API_ROOT + 'flight-check?flight=' + encodeURIComponent(q.f) + '&date=' + q.d + '&airport=' + q.ap + '&dir=' + q.dir, { credentials: 'same-origin', cache: 'no-store' })
    .then(function (r) { return r.json(); })
    .then(function (j) {
      if (FLC.key !== q.k) return FLC;
      FLC = { key: q.k, status: j.status || 'unavailable', message: j.message || '', leg: j.leg || null, dir: q.dir };
      if (FLC.status === 'ok' && q.dir === 'arr' && FLC.leg && FLC.leg.arr_time && !FCPV('time')) setField('time', FLC.leg.arr_time);
      if (comp) comp.setState({ flT: Date.now() });
      return FLC;
    }).catch(function () { FLC = { key: q.k, status: 'unavailable', message: '' }; if (comp) comp.setState({ flT: Date.now() }); return FLC; });
}
API.scheduleFlightCheck = function (comp) { clearTimeout(flTimer); flTimer = setTimeout(function () { checkFlight(comp); }, 700); };
API.flight = function (v) {
  var t = String(v || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
  if (!t) return { show: false, hide: true };
  var m = t.match(/^([A-Z0-9]{2})(\d{1,4})[A-Z]?$/);
  if (!m || !/[A-Z]/.test(m[1])) {
    var tg = flightTarget();
    if (tg && tg.ap === 'LBG' && /^[A-Z0-9-]{4,8}$/.test(String(v).toUpperCase().replace(/\s+/g, ''))) return { show: true, hide: false, color: 'rgba(11,31,58,.65)', text: 'Vol privé · immatriculation ' + String(v).toUpperCase().trim(), url: '' };
    return { show: true, hide: false, color: '#9B2C2C', text: 'Format attendu : 2 caractères puis le numéro, par exemple AF 1234', url: '' };
  }
  var al = AIRLINES[m[1]], code = m[1] + ' ' + m[2];
  var url = 'https://www.google.com/search?q=' + encodeURIComponent('vol ' + m[1] + m[2]);
  var base = al ? { show: true, hide: false, color: '#2F5D3A', text: '✓ ' + al + ' · vol ' + code, url: url, al: al, code: code }
                : { show: true, hide: false, color: '#9B5B1E', text: 'Compagnie « ' + m[1] + ' » non reconnue · vérifiez le numéro', url: url, code: code };
  var q = flightKey();
  if (q && FLC.key === q.k) {
    if (FLC.status === 'pending') { base.text = (al ? al + ' · vol ' + code : 'Vol ' + code) + ' · vérification en cours…'; base.color = 'rgba(11,31,58,.6)'; }
    else if (FLC.status === 'ok' && FLC.leg) {
      var L = FLC.leg;
      base.text = '✓ ' + (L.airline || al || 'Vol') + ' · ' + code + ' · ' + L.from + ' → ' + L.to + (q.dir === 'arr' ? (L.arr_time ? ' · atterrissage ' + L.arr_time : '') : (L.dep_time ? ' · décollage ' + L.dep_time : ''));
      base.color = '#2F5D3A'; base.verified = true;
    } else if (FLC.status === 'mismatch' || FLC.status === 'not_found') { base.text = '✕ ' + FLC.message; base.color = '#9B2C2C'; base.blocked = true; }
  }
  return base;
};

/* ---------- Route detection from the two addresses ---------- */
function zoneOf(t) {
  var pc = String(t || '').match(/\b(\d{5})\b/), n = norm(t);
  if (pc) {
    var d = pc[1].slice(0, 2);
    if (d === '75') return 'paris';
    if (d === '92' || d === '93' || d === '94') return 'petite';
    if (d === '77' || d === '78' || d === '91' || d === '95') return 'grande';
    return 'hors';
  }
  if (/\bparis\b/.test(n)) return 'paris';
  if (/versailles/.test(n)) return 'grande';
  return null;
}
function kindOf(t) {
  var n = norm(t);
  if (!n) return null;
  if (/\bcdg\b|charles de gaulle|\broissy\b/.test(n)) return 'cdg';
  if (/aeroport.*orly|orly.*(aeroport|terminal)|\bory\b|^orly$/.test(n)) return 'orly';
  if (/aeroport.*bourget|bourget.*(aeroport|aviation)|\blbg\b/.test(n)) return 'lbg';
  if (/beauvais/.test(n)) return 'bva';
  if (/disney|chessy|marne la vallee/.test(n)) return 'disney';
  if (/^gare\b/.test(n)) return 'gare';
  return null;
}
var RATES = { c: 2.50, e: 3.13, s: 5.63, v: 3.50 }, DIST = {}, KM = { km: 0 };
function hav(a, b) { var R = 6371, r = Math.PI / 180, dl = (b[0] - a[0]) * r, dn = (b[1] - a[1]) * r; var x = Math.sin(dl / 2) * Math.sin(dl / 2) + Math.cos(a[0] * r) * Math.cos(b[0] * r) * Math.sin(dn / 2) * Math.sin(dn / 2); return 2 * R * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x)); }
var kmComp = null;
API.kmGrid = function (base) {
  var a = VALS.origin_ll, b = VALS.destination_ll;
  KM.km = 0;
  if (!a || !b || !base) return null;
  var key = a.join(',') + ';' + b.join(',');
  if (DIST[key] == null) {
    DIST[key] = Math.round(hav(a, b) * 1.3);
    fetch('https://router.project-osrm.org/route/v1/driving/' + a[1] + ',' + a[0] + ';' + b[1] + ',' + b[0] + '?overview=false', { mode: 'cors' })
      .then(function (r) { return r.json(); })
      .then(function (j) { var d = j && j.routes && j.routes[0] && j.routes[0].distance; if (d) { DIST[key] = Math.round(d / 1000); if (kmComp) kmComp.setState({ kmT: Date.now() }); } })
      .catch(function () {});
  }
  var km = DIST[key]; KM.km = km;
  var g = {};
  ['c', 'e', 's', 'v'].forEach(function (k) { g[k] = Math.max(base[k] || 0, Math.ceil(km * RATES[k] / 5) * 5); });
  return g;
};
API.kmInfo = function () { return KM.km ? KM.km + ' km' : ''; };
/* ---------- City tours & excursions (règle : 20 km inclus par heure) ---------- */
var KM_PER_H = 20;
var TOURS = [
  { k: 'classique', n: 'Paris classique', h: 3, d: 'Tour Eiffel, Trocadéro, Champs-Élysées, Concorde, Louvre, Notre-Dame, Saint-Germain-des-Prés.' },
  { k: 'nuit', n: 'Paris by night', h: 3, d: 'Paris illuminé : Tour Eiffel scintillante, Champs-Élysées, Opéra, quais de Seine.' },
  { k: 'shopping', n: 'Paris shopping', h: 4, d: 'Avenue Montaigne, Faubourg Saint-Honoré, place Vendôme, grands magasins : votre chauffeur vous attend à chaque adresse.' }
];
var EXCS = [
  { k: 'versailles', n: 'Château de Versailles', h: 6, km: 40, P: { c: 535, e: 625, v: 720, s: 1000 }, d: 'Le château, la galerie des Glaces et les jardins à la française.' },
  { k: 'giverny', n: 'Giverny · jardins de Monet', h: 6, km: 160, P: { c: 595, e: 700, v: 805, s: 1115 }, d: 'La maison et les jardins de Claude Monet, le bassin aux nymphéas.' },
  { k: 'fontainebleau', n: 'Fontainebleau & Barbizon', h: 8, km: 140, P: { c: 755, e: 885, v: 1020, s: 1415 }, d: 'Le château des rois de France et le village des peintres.' },
  { k: 'champagne', n: 'Champagne · Reims & Épernay', h: 10, km: 300, P: { c: 835, e: 980, v: 1130, s: 1570 }, d: 'La cathédrale de Reims, une grande maison de champagne, un vigneron et l’avenue de Champagne.' },
  { k: 'mesure', n: 'Votre journée sur mesure', h: 0, km: 0, P: null, d: 'Châteaux de la Loire, Normandie, Mont-Saint-Michel, Chantilly ou votre propre itinéraire : la Maison vous adresse un devis.' }
];
function guideFee(x) { return x.h <= 6 ? 300 : 450; }
var TIER_NAMES = { c: 'Confort', e: 'Business', s: 'Signature', v: 'Van' };
function eur(n) { return String(n.toFixed(2)).replace('.', ',') + ' €'; }
function cardStyle(on) { return { sel: on ? 'true' : 'false', bg: on ? '#0B1F3A' : '#FFFFFF', fg: on ? '#F6F1E7' : '#0B1F3A', border: on ? '#0B1F3A' : 'rgba(11,31,58,.2)' }; }
function tourOf(S) { return TOURS.filter(function (t) { return t.k === S.bTour; })[0] || TOURS[0]; }
function excOf(S) { return EXCS.filter(function (x) { return x.k === S.bExc; })[0] || EXCS[0]; }
API.tourGrid = function (S, HOURLY) { var t = tourOf(S), g = {}; ['c', 'e', 's', 'v'].forEach(function (k) { g[k] = HOURLY[k] * t.h; }); return g; };
API.excGrid = function (S) {
  var x = excOf(S), g = {}; if (!x.P) return null;
  ['c', 'e', 's', 'v'].forEach(function (k) { g[k] = x.P[k] + (S.bGuide ? guideFee(x) : 0); });
  return g;
};
API.tourCards = function (S, set, tier, fmt) {
  var cur = tourOf(S).k, t = TIER_NAMES[tier] ? tier : 'e', H = { c: 75, e: 90, s: 140, v: 100 };
  return TOURS.map(function (x) { var o = cardStyle(x.k === cur); o.n = x.n; o.d = x.d; o.meta = x.h + ' h · ' + fmt(H[t] * x.h); o.pick = function () { set({ bTour: x.k }); }; return o; });
};
API.excCards = function (S, set, tier, fmt) {
  var cur = excOf(S).k, t = TIER_NAMES[tier] ? tier : 'e';
  return EXCS.map(function (x) { var o = cardStyle(x.k === cur), g = API.excGrid({ bExc: x.k, bGuide: S.bGuide }); o.n = x.n; o.d = x.d; o.meta = g ? x.h + ' h · ' + fmt(g[t]) : 'Sur devis'; o.pick = function () { set({ bExc: x.k }); }; return o; });
};
API.excOpts = function (S, set, fmt) {
  var x = excOf(S), on = function (a) { return { sel: a ? 'true' : 'false', bg: a ? '#0B1F3A' : '#FFFFFF', fg: a ? '#F6F1E7' : '#0B1F3A', border: a ? '#0B1F3A' : 'rgba(11,31,58,.2)' }; };
  return { show: !!x.P,
    formulas: [{ n: 'Visite libre', a: !S.bGuide, v: false }, { n: 'Avec guide-conférencier · + ' + fmt(guideFee(x)), a: !!S.bGuide, v: true }].map(function (f) { var o = on(f.a); o.n = f.n; o.pick = function () { set({ bGuide: f.v }); }; return o; }),
    tickets: (function () { var o = on(!!S.bTickets); var ch = x.k === 'champagne'; o.n = S.bTickets ? (ch ? '✓ La Maison réserve vos visites et dégustations' : '✓ La Maison réserve vos billets') : (ch ? 'Réserver les visites et dégustations' : 'Réserver les billets d’entrée'); o.pick = function () { set({ bTickets: !S.bTickets }); }; return o; })(),
    ticketNote: x.k === 'champagne' ? 'Visites et dégustations au tarif des maisons, par personne, ajoutées à la confirmation.' : 'Billets au tarif officiel, par personne, ajoutés à la confirmation.' };
};
API.kmRule = function (S, tier) {
  var t = RATES[tier] ? tier : 'e';
  if (S.bMode === 'excursion') { var x = excOf(S); if (!x.P) return 'Itinéraire, durée et tarif établis sur devis par la Maison. Précisez vos envies dans votre message.'; return 'Prix par véhicule, selon la gamme : ' + x.h + ' h avec votre chauffeur, environ ' + x.km + ' km aller-retour compris. Heure supplémentaire au tarif horaire de la gamme.'; }
  return KM_PER_H + ' km inclus par heure de mise à disposition. Au-delà, le kilomètre est facturé ' + eur(RATES[t]) + ' en gamme ' + TIER_NAMES[t] + '.';
};
API.offerLabel = function (S) {
  if (S.bMode === 'tour') { var t = tourOf(S); return t.n + ' · ' + t.h + ' h'; }
  var x = excOf(S); if (!x.P) return x.n + ' · sur devis';
  return x.n + ' · ' + x.h + ' h · ' + (S.bGuide ? 'avec guide-conférencier' : 'visite libre') + (S.bTickets ? (x.k === 'champagne' ? ' · visites et dégustations à réserver par la Maison' : ' · billets à réserver par la Maison') : '');
};
var MODES_OK = null;
API.modeAllowed = function (k) { return !MODES_OK || MODES_OK.indexOf(k) > -1; };
API.allowModes = function (list) { MODES_OK = list; };
API.outsideParis = function () {
  var a = FCPV('origin') || FCPV('qfrom') || '', b = FCPV('destination') || '';
  var other = kindOf(a) ? b : kindOf(b) ? a : '';
  if (!other || kindOf(other)) return false;
  var z = zoneOf(other);
  return !!z && z !== 'paris';
};
API.detectRoute = function (comp) {
  var S = comp.state;
  if (S.bMode !== 'transfer' || S.routeManual) return;
  var a = FCPV('origin') || FCPV('qfrom') || '', b = FCPV('destination') || '';
  if (!a || !b) return;
  var ka = kindOf(a), kb = kindOf(b), AP = { cdg: 1, orly: 1, lbg: 1 }, route = null, zone = null;
  if ((ka === 'cdg' && kb === 'orly') || (ka === 'orly' && kb === 'cdg')) route = 'cdgorly';
  else if (AP[ka] || AP[kb]) route = 'airport';
  else if (ka === 'bva' || kb === 'bva') route = 'beauvais';
  else if (ka === 'disney' || kb === 'disney') route = 'disney';
  else if ((ka === 'gare' || kb === 'gare') && (zoneOf(ka === 'gare' ? b : a) || 'paris') === 'paris') route = 'gare';
  else {
    var za = zoneOf(a) || (ka === 'gare' ? 'paris' : null), zb = zoneOf(b) || (kb === 'gare' ? 'paris' : null);
    if (!za || !zb) return;
    var R = { paris: 0, petite: 1, grande: 2, hors: 3 }, K = ['paris', 'petite', 'grande', 'hors'];
    route = 'ville'; zone = K[Math.max(R[za], R[zb])];
  }
  var next = { bRoute: route, routeAuto: true };
  if (zone) next.bZone = zone;
  if (S.bRoute !== route || (zone && S.bZone !== zone) || !S.routeAuto) comp.setState(next);
};

/* ---------- Mount ---------- */
var WRAP_CSS =
  '#fcp-home{width:100%;position:relative;font-size:16px;line-height:normal;font-weight:400;-webkit-font-smoothing:antialiased;text-align:left}' +
  '#fcp-home *,#fcp-home *:before,#fcp-home *:after{box-sizing:content-box}' +
  '#fcp-home ul,#fcp-home ol{padding:0;margin:0;list-style:none}' +
  '#fcp-home input,#fcp-home select,#fcp-home textarea{box-shadow:none}' +
  '#fcp-home input:focus,#fcp-home select:focus,#fcp-home textarea:focus{outline:none;box-shadow:0 1px 0 #C6A15B}' +
  '#fcp-home img{max-width:none}' +
  '#fcp-home p,#fcp-home h1,#fcp-home h2,#fcp-home h3{padding-bottom:0}' +
  '#fcp-home button{cursor:pointer}' +
  'html.fcp-home-mobile,html.fcp-home-mobile body{overflow:hidden;height:100%}' +
  'html.fcp-home-mobile #fcp-home{position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:99990;background:#0B1F3A;-webkit-transform:none;transform:none}' +
  'html.fcp-home-mobile body>*:not(#fcp-home):not(#fcp-ac){display:none!important}' +
  'html.fcp-home-mobile body{margin:0!important;padding:0!important;background:#0B1F3A!important}' +
  'html.fcp-home-mobile .joinchat,html.fcp-home-mobile .gt_switcher_wrapper,html.fcp-home-mobile #wpadminbar{display:none!important}' +
  'body.fcp-home-page .main_title,body.fcp-home-page #sidebar{display:none!important}' +
  'body.fcp-home-page #main-content .container{width:100%!important;max-width:none!important;padding:0!important}' +
  'body.fcp-home-page #main-content .container:before{display:none!important}' +
  'body.fcp-home-page #left-area{width:100%!important;padding:0!important;float:none!important}' +
  'body.fcp-home-page #main-content,body.fcp-home-page .entry-content{background:transparent;padding:0;margin:0}' +
  'body.fcp-home-page article.page{padding:0!important;margin:0!important}' +
  '@media (max-width:899px){body.fcp-svc-page .joinchat,body.fcp-svc-page .gt_switcher_wrapper{display:none!important}}';

function addStyle(id, css) { var s = document.getElementById(id); if (!s) { s = document.createElement('style'); s.id = id; document.head.appendChild(s); } s.textContent = css; }

function showError(root, e) {
  try {
    root.removeAttribute('style');
    root.innerHTML = '<div style="min-height:100vh;background:#0B1F3A;color:#F6F1E7;padding:48px 24px;font:16px/1.5 Georgia,serif">'
      + '<p style="font-size:26px;margin:0 0 12px">French Class Prestige</p>'
      + '<p style="margin:0 0 24px">La page n’a pas pu s’afficher sur cet appareil.</p>'
      + '<a href="' + WA + '" style="display:inline-block;padding:14px 22px;background:#C6A15B;color:#0B1F3A;text-decoration:none;font:500 13px/1 sans-serif;letter-spacing:2px;text-transform:uppercase">Écrire sur WhatsApp</a>'
      + '<p style="margin:32px 0 0;font:12px/1.4 monospace;color:rgba(246,241,231,.6)">' + str(String((e && (e.stack || e.message)) || e)).slice(0, 400) + '</p></div>';
  } catch (x) {}
}

var AIRPORTS = { cdg: 'Aéroport Paris-Charles de Gaulle (CDG)', orly: 'Aéroport Paris-Orly (ORY)', lbg: 'Aéroport Paris-Le Bourget (LBG)', bva: 'Aéroport Paris-Beauvais (BVA)' };
function deepLink(inst) {
  var h = location.hash || '';
  if (!inst || !inst.comp || h.indexOf('#reserver') !== 0) return;
  var q = {}; (h.split('?')[1] || '').split('&').forEach(function (kv) { var p = kv.split('='); if (p[0]) q[p[0]] = decodeURIComponent((p[1] || '').replace(/\+/g, ' ')); });
  var ap = AIRPORTS[q.ap] || q.place || '';
  if (ap) { if (q.dir === 'dep') { VALS.destination = ap; if (q.addr) VALS.origin = q.addr; } else { VALS.origin = ap; if (q.addr) VALS.destination = q.addr; } }
  else if (q.addr) VALS.origin = q.addr;
  var apll = PLACE_LL[ap] || null, all = q.ll ? q.ll.split(',').map(Number) : null;
  if (ap) { if (q.dir === 'dep') { VALS.destination_ll = apll; VALS.origin_ll = all; } else { VALS.origin_ll = apll; VALS.destination_ll = all; } }
  if (q.date) VALS.date = q.date;
  if (q.flight) VALS.flight = q.flight;
  if (q.modes) MODES_OK = q.modes.split(',');
  var st = { sheet: 'booking', bStep: 'form', bMode: q.mode || 'transfer', requested: false };
  if (q.route) { st.bRoute = q.route; st.routeManual = true; }
  if (q.tier) st.bTier = q.tier;
  if (q.greeter === '1') st.greeter = true;
  if (q.exc) st.bExc = q.exc;
  if (q.guide === '1') st.bGuide = true;
  if (q.tickets === '1') st.bTickets = true;
  if (q.tour) st.bTour = q.tour;
  inst.comp.setState(st);
  try { history.replaceState(null, '', location.pathname + location.search); } catch (e) {}
}

API.goBook = function (o) {
  var p = ['route=' + (o.route || ''), 'tier=' + (o.tier || 'e'), 'dir=' + (o.dir || 'arr'), 'ap=' + (o.ap || ''), 'modes=' + (o.modes || 'transfer,dispo')];
  if (o.mode) p.push('mode=' + o.mode);
  if (o.exc) p.push('exc=' + o.exc);
  if (o.guide) p.push('guide=1');
  if (o.tickets) p.push('tickets=1');
  if (o.tour) p.push('tour=' + o.tour);
  ['addr', 'date', 'flight'].forEach(function (k) { if (FCPV(k)) p.push(k + '=' + encodeURIComponent(FCPV(k))); });
  if (VALS.addr_ll) p.push('ll=' + VALS.addr_ll.join(','));
  if (o.greeter) p.push('greeter=1');
  location.href = (window.FCP_HOME_URL || '/') + '#reserver?' + p.join('&');
};

function boot() {
  var root = document.getElementById('fcp-home');
  if (!root) { root = document.createElement('div'); root.id = 'fcp-home'; document.body.insertBefore(root, document.body.firstChild); }
  root.removeAttribute('style');
  root.innerHTML = '';
  var home = document.createComment('fcp-home');
  root.parentNode.insertBefore(home, root);
  document.body.classList.add('fcp-home-page');
  if (!LAYOUTS.m) document.body.classList.add('fcp-svc-page');
  if (!document.querySelector('link[data-fcp-fonts]')) { var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = FONTS; l.setAttribute('data-fcp-fonts', ''); document.head.appendChild(l); }
  addStyle('fcp-home-wrap', WRAP_CSS);
  try { acInit(); } catch (e) {}
  var cur = null, inst = null;
  function pick() { return !LAYOUTS.m ? 'd' : window.innerWidth >= 1024 ? 'd' : 'm'; }
  function mount() {
    var k = pick(); if (k === cur) return;
    if (inst) inst.destroy();
    cur = k;
    if (k === 'm') { if (root.parentNode !== document.body) document.body.appendChild(root); }
    else if (home.parentNode && root.previousSibling !== home) home.parentNode.insertBefore(root, home.nextSibling);
    document.documentElement.classList.toggle('fcp-home-mobile', k === 'm');
    addStyle('fcp-home-layout', LAYOUTS[k].css.replace(/(^|\})\s*body\s*\{margin:0\}/g, '$1'));
    try { inst = new Instance(LAYOUTS[k], root); } catch (e) { inst = null; showError(root, e); if (window.console) console.error(e); }
  }
  mount();
  try { deepLink(inst); } catch (e) {}
  var t; window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(mount, 150); });
}
function safeBoot() { try { boot(); } catch (e) { var r = document.getElementById('fcp-home'); if (r) showError(r, e); } }
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', safeBoot); else safeBoot();

})();
