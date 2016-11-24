```php
    /**
     * Constructor
     */
    public function __construct(
        array $definitions = [],
        ContainerInterface $parent = null,
        array $children = [],
        LoggerInterface $logger = null
    )
    
    /**
     * Append a child container
     *
     * @param  ContainerInterface $child Child container
     * @return void
     */
    public appendChild(ContainerInterface $child)
    
    /**
     * Define an entity
     *
     * @param  string  $id       Entity identifier
     * @param  mixed   $entity   Entity factory callable, class name or entity
     * @param  mixed[] $params   Parameters for entity construction
     * @param  bool    $register Whether the entity should become shared
     * @return bool              `true` on success, `false` otherwise
     */
    public define(string $id, $entity, array $params = [], bool $register = true): bool
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public get($id)
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * 
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public has($id)
    
    /**
     * Prepend a child container
     *
     * @param  ContainerInterface $child Child container
     * @return void
     */
    public prependChild(ContainerInterface $child)
    
    /**
     * Register an entity as shared
     *
     * @param  string $id     Entity identifier
     * @param  mixed  $entity Entity to share
     * @return bool           `true` on success, `false` otherwise
     */
    public register(string $id, $entity): bool
    
    /**
     * LoggerAware implementation
     *
     * @param  LoggerInterface $logger
     * @return void
     */
    public setLogger(LoggerInterface $logger)
    
    /**
     * Set the parent container
     *
     * @param  ContainerInterface $parent Parent container
     * @return void
     */
    public setParent(ContainerInterface $parent)
```
