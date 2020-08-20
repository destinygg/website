<?php
namespace Destiny\Chat;

class BanReasonParser {
    const BOT_USERNAME = 'Bot';

    public $rules;    

    function __construct(array $rules) {
        $this->rules = $rules;
    }

    public function transformBan($ban): array {
        // Non-Bot-issued bans don't have ban reasons that qualify for parsing.
        if (!$this->isBotBan($ban)) {
            return $ban;
        }

        foreach ($this->rules as $rule) {
            $matches = $rule->test($ban);
            if (!empty($matches)) {
                $ban = $rule->apply($ban, $matches);
                break;
            }
        }

        return $ban;
    }

    public function isBotBan($ban): bool {
        return $ban['banningusername'] === BanReasonParser::BOT_USERNAME;
    }
}
