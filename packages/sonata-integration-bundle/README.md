# Sonata Integration Bundle

This bundle is for draw/* component integration in sonata.

## Console

The configuration lists the commands available from Sonata to be selected and executed:

```YAML
draw_sonata_integration:
    console:
        admin:
           
        commands:
            clearCache:
                commandName: "redis:flushdb"
                label: "Clear Cache"
                icon: "fa-ban"
            reIndexSearch:
                commandName: "fos:elastica:populate"
                label: "Re-Index Search"
                icon: "fa-search-plus"
```