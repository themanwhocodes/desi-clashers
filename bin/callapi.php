<?php

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/ClashAPI/API.class.php');

date_default_timezone_set('Europe/Berlin');

$redis = new Predis\Client(getenv('REDIS_URL'));

if ($redis->exists('timer')) {

  echo 'Just wait another hour...';

}
else {

$timestamp = date("d.m.Y - H:i");

$clan = new CoC_Clan("#QVQRYYG");


$tottroph = 0;
$totlvl = 0;

foreach ($clan->getAllMembers() as $clanmember)
{
	$member = new CoC_Member($clanmember);
  $league = new CoC_League($member->getLeague());
  $donationsReceivedCalc = $member->getDonationsReceived();
	if ($donationsReceivedCalc == 0) $donationsReceivedCalc++;

	$ratio = $member->getDonations() / $donationsReceivedCalc;

  $tottroph = $tottroph + $member->getTrophies();
  $totlvl = $totlvl + $member->getLevel();

  $clanmem[$member->getClanRank()] = [
    "rank" => $member->getClanRank(),
    "prevrank" => $member->getPreviousClanRank(),
    "name" => $member->getName(),
    "role" => $member->getRole(),
    "trophies" => $member->getTrophies(),
    "donations" => $member->getDonations(),
    "received" => $member->getDonationsReceived(),
    "ratio" => number_format($ratio, 2),
    "level" => $member->getLevel(),
    "leaguename" => $league->getLeagueName(),
    "leagueid" => $league->getLeagueId(),
    "leagueicontn" => $league->getLeagueIcon("tiny"),
    "leagueiconsm" => $league->getLeagueIcon("small"),
    "leagueiconmd" => $league->getLeagueIcon("medium"),
  ];

}

$avgtroph = round($tottroph / $clan->getMemberCount(), 0);
$avglvl = round($totlvl / $clan->getMemberCount(), 0);

$logcount = 1;

foreach ($clan->getClanWarlogByTag() as $warlog)
{
	$log = new CoC_Warlog($warlog);

  $warlog[$logcount] = [
    "result" => $warlog->getResult(),
    "endtime" => $warlog->getEndtime(),
    "size" => $warlog->getTeamsize(),
    "ctag" => $warlog->getClanTag(),
    "cname" => $warlog->getClanName(),
    "clvl" => $warlog->getClanLevel(),
    "cattacks" => $warlog->getClanAttacks(),
    "cstars" => $warlog->getClanStars(),
    "cdestruct" => $warlog->getClanDestruction(),
    "cexp" => $warlog->getClanExp(),
    "cbadgesm" =>  $warlog->getClanBadgeUrl("small"),
    "cbadgemd" =>  $warlog->getClanBadgeUrl("medium"),
    "cbadgelg" =>  $warlog->getClanBadgeUrl("large"),
    "otag" => $warlog->getOpponentTag(),
    "oname" => $warlog->getOpponentName(),
    "olvl" => $warlog->getOpponentLevel(),
    "oattacks" => $warlog->getOpponentAttacks(),
    "ostars" => $warlog->getOpponentStars(),
    "odestruct" => $warlog->getOpponentDestruction(),
    "oexp" => $warlog->getOpponentExp(),
    "obadgesm" =>  $warlog->getOpponentBadgeUrl("small"),
    "obadgemd" =>  $warlog->getOpponentBadgeUrl("medium"),
    "obadgelg" =>  $warlog->getOpponentBadgeUrl("large"),
  ];

$logcount++;

}



$clandetails = [
  "badgesm" =>  $clan->getBadgeUrl("small"),
  "badgemd" =>  $clan->getBadgeUrl("medium"),
  "badgelg" =>  $clan->getBadgeUrl("large"),
  "name" => $clan->getName(),
  "level" => $clan->getLevel(),
  "description" => $clan->getDescription(),
  "wins" => $clan->getWarWins(),
  "ties" => $clan->getWarTies(),
  "losses" => $clan->getWarLosses(),
  "streak" => $clan->getWarWinStreak(),
  "points" => $clan->getPoints(),
  "freq" => $clan->getWarFrequency(),
  "membercount" => $clan->getMemberCount(),
  "avgtroph" => $avgtroph,
  "avglvl" => $avglvl,
  "timestamp" => $timestamp,
];

$redis->set('clandetails', serialize($clandetails));
$redis->set('clanmem', serialize($clanmem));
$redis->set('warlog', serialize($warlog));
$redis->setEx('timer', 5400, '');

}
