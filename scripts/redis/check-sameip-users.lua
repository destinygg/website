if not ARGV[1] then
  return {err = "INVALID ARGUMENTS"}
end

local uidkey = "CHAT:userips-" .. ARGV[1]
local foundkeys = {}
local keys = redis.call("KEYS", "CHAT:userips-*")
for _, key in ipairs(keys) do
  if key ~= uidkey then
    local sameiplength = redis.call("ZINTERSTORE", "sameips", 2, uidkey, key)
    if sameiplength ~= 0 then
      table.insert(foundkeys, key)
    end
  end
end

redis.call("DEL", "sameips")
return foundkeys
