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
$wl = new CoC_Warlog("#QVQRYYG");

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

foreach ($wl->getItems() as $warlog)
{
  $log = new CoC_Warlog($warlog);

  $wars[$logcount] = [
    "result" => $log->getResult(),
    "endtime" => $log->getEndtime(),
    "size" => $log->getTeamsize(),
    "ctag" => $log->getClanTag(),
    "cname" => $log->getClanName(),
    "clvl" => $log->getClanLevel(),
    "cattacks" => $log->getClanAttacks(),
    "cstars" => $log->getClanStars(),
    "cdestruct" => $log->getClanDestruction(),
    "cexp" => $log->getClanExp(),
    "cbadgesm" =>  $log->getClanBadgeUrl("small"),
    "cbadgemd" =>  $log->getClanBadgeUrl("medium"),
    "cbadgelg" =>  $log->getClanBadgeUrl("large"),
    "otag" => $log->getOpponentTag(),
    "oname" => $log->getOpponentName(),
    "olvl" => $log->getOpponentLevel(),
    "oattacks" => $log->getOpponentAttacks(),
    "ostars" => $log->getOpponentStars(),
    "odestruct" => $log->getOpponentDestruction(),
    "oexp" => $log->getOpponentExp(),
    "obadgesm" =>  $log->getOpponentBadgeUrl("small"),
    "obadgemd" =>  $log->getOpponentBadgeUrl("medium"),
    "obadgelg" =>  $log->getOpponentBadgeUrl("large"),
  ];

$logcount++;

}

var_dump($wars);


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
$redis->set('warlog', serialize($wars));
$redis->setEx('timer', 5400, '');

}
