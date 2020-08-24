<?php
namespace Destiny\Chat;

use Destiny\Chat\BanReasonParser;
use Destiny\Chat\BanReasonParseRule;

class BanReasonParserFactory {
    public static function create(): BanReasonParser {
        $rules = [
            new BanReasonParseRule(
                '/^MEGA NUKED by (?P<banningusername>\w+)$/',
                function($ban, $matches) {
                    // If `banningusername` changes, toggle this value so we
                    // still know it's a Bot ban.
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'Blasted by a MEGA NUKE.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+)\n       BANNED for (?P<duration>.+) for using a recently MEGA NUKED phrase \((?P<meganukedphrase>.+)\)\.$/',
                function($ban, $matches) {
                    $ban['reason'] = 'Blasted by a MEGA NUKE for ' . $matches['duration'] . ' for typing `' . $matches['meganukedphrase'] . '`.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by (?P<banningusername>\w+)\. \n     Reason: (?P<reason>.+)$/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = $matches['reason'];
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by (?P<banningusername>\w+)\. \n    $/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'No reason provided.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned for using banned phrase\((?P<bannedphrase>.+)\)\. Length: (?P<banlength>.+)\.$/',
                function($ban, $matches) {
                    $ban['reason'] = 'Banned for ' . $matches['banlength'] . ' for using banned phrase `' . $matches['bannedphrase'] . '`.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned for using banned phrase\. Length: (?P<banlength>.+)\.$/',
                function($ban, $matches) {
                    $ban['reason'] = 'Banned for ' . $matches['banlength'] . ' for using banned phrase.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by a VOTE BAN started by (?P<banningusername>\w+)\. Reason: (?P<reason>.+) Yes votes: (?P<yesvotes>\d+) No Votes: (?P<novotes>\d+)$/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'Banned via VOTE BAN with ' . $matches['yesvotes'] . ' YES votes and ' . $matches['novotes'] . ' NO votes. Reason: ' . $matches['reason'];
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by a VOTE BAN started by (?P<banningusername>\w+)\.  Yes votes: (?P<yesvotes>\d+) No Votes: (?P<novotes>\d+)$/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'Banned via VOTE BAN with ' . $matches['yesvotes'] . ' YES votes and ' . $matches['novotes'] . ' NO votes.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by a VOTE BAN started by (?P<banningusername>\w+)\.$/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'Banned via VOTE BAN.';
                    return $ban;
                }
            ),
            new BanReasonParseRule(
                '/^(?P<targetusername>\w+) banned through bot by GULAG battle started by (?P<banningusername>\w+)\. Votes: (?P<votes>\d+)$/',
                function($ban, $matches) {
                    $ban['botban'] = true;
                    $ban['banningusername'] = $matches['banningusername'];
                    $ban['reason'] = 'Bested in the GULAG where they received only ' . $matches['votes'] . ' votes.';
                    return $ban;
                }
            )
        ];

        return new BanReasonParser($rules);
    }
}
