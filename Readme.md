# Pets Plugin for PocketMine-MP

A feature-rich pets plugin that allows players to spawn and manage various mobs as pets that follow them around.

## Features

- **Multiple Pet Types**: Supports various mobs including:
  - (Zombie, Skeleton, Creeper, Spider, Blaze, Enderman, Ghast, Slime, Magma Cube, Witch)
  - (Cow, Pig, Sheep, Chicken, Wolf, Villager, Squid)
- **Custom Pet Names**: Players can give their pets custom names
- **Permission-Based System**: Control which players can spawn specific pet types
- **Pet Management**: Easy-to-use commands and forms interface
- **Automatic Following**: Pets automatically follow their owners
- **Persistent Pets**: Pets are saved and respawn when players rejoin
- **World Blacklisting**: Disable pets in specific worlds

## Usage

### Commands

- `/pets` or `/pet` - Opens the pet management menu
  - Select a pet type to spawn
  - Name your pet
  - Remove current pet

### Permissions

- `pets.command` - Allows usage of the /pets command (default: true)
- `pets.type.<mobname>` - Allows spawning specific pet types (default: op)
  - Example: `pets.type.zombie`, `pets.type.cow`, etc.

### Pet Types Available

- Zombie
- Skeleton
- Creeper
- Spider
- Blaze
- Enderman
- Ghast
- Slime
- Magma Cube
- Witch
- Cow
- Pig
- Sheep
- Chicken
- Wolf
- Villager
- Squid

(I'll add more when i feel like it.)

## Configuration

Plugin configuration is handled through the `config.yml` file and permissions.

### config.yml

A `config.yml` file is now included to allow blacklisting worlds where pets are disabled.  Add the folder names of the worlds to the `blacklisted-worlds` list.

```yaml
# List of worlds where pets are disabled
# Add world folder names to this list to blacklist them
blacklisted-worlds:
  - "world1"
```

### Permissions

Adjust the permissions in your server's permission manager or `permissions.yml` to control which players can access specific pet types. By default, pet types are set to `default=false`.

## Features In Detail

- **Smart Following**: Pets will intelligently follow players, including climbing blocks and avoiding obstacles
- **World Management**: Pets will teleport to players when changing worlds. Pets are automatically removed in blacklisted worlds.
- **Distance Management**: Pets automatically teleport to players if they get too far away
- **Form Interface**: User-friendly form interface for pet management
- **Name Customization**: Optional custom naming for pets
- **Persistence**: Pets are saved between server restarts
- **Cleanup System**: Automatic cleanup of old pet entities
- **World Blacklisting**: Pets can be disabled in specific worlds by adding the world's folder name to the `blacklisted-worlds` list in the `config.yml` file. Players will receive a message when entering a blacklisted world.

## Support

For support, please create an issue on the plugin's GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Created by Taqdees

(I Stole the icon file from here
https://icons8.com/icons/set/minecraft--animated
</3
)

## Dependencies

FormAPI
