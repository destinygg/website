if not ARGV[1] then
  return {err = "INVALID ARGUMENTS"}
end

local ip = ARGV[1]
local foundusers = {}
local keys = redis.call("KEYS", "CHAT:userips-*")
for _, key in ipairs(keys) do
    local userIps = redis.call('ZRANGE', key, 0, -1);
    for _, userIp in ipairs(userIps) do
        if string.match(userIp, ip) then
           table.insert(foundusers, key)
        end
    end
end

return foundusers
