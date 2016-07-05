# zf2-phing-task

A collection of Phing Tasks for a ZF2 app.

Note: it will look for `config/application.config.yml` instead of
`config/application.config.php` on bootstrap. If you use PHP config files,
you'll have to change the code in each Task's `main()` method.

# Installation

[Composer](http://getcomposer.org/):

```json
{
    "require": {
        "yalesov/zf2-phing-task": "1.*"
    }
}
```

[Phing](https://github.com/phingofficial/phing) is **not** bundled with this package.

You can install it through Composer and use the CLI at `vendor/bin/phing`:

```json
{
    "require": {
        "phing/phing": "*"
    }
}
```

or through PEAR and use the CLI at `phing`:

```sh
$ pear channel-discover pear.phing.info
$ pear install [--alldeps] phing/phing
```

# Usage

You must initialize an instance of your Zf2 application through **ZfTask** before using any of the remaining Tasks.

## ZfTask

Bootstrap the ZF2 application using the file `foo/bootstrap.php`. The bootstrap file must return an instance of `Zend\Mvc\Application`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="zf" classname="ZfTask" />
        <zf bootstrap="foo/bootstrap.php" />
    </target>
</project>
```

`foo/bootstrap.php`:

```php
/* do some bootstrap */
$application = Zend\Mvc\Application::init(/* config array */);
return $application;
```

## DoctrineEntityTask

Generate entities for the EntityManager `doctrine.entitymanager.orm_default`, base directory at `foo/src`, with filter `Foo\Entity`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="d-entity" classname="DoctrineEntityTask" />
        <d-entity em="doctrine.entitymanager.orm_default" filter="Foo\Entity" output="foo/src" />
    </target>
</project>
```

## DoctrineRepoTask

Generate repositories for the EntityManager `doctrine.entitymanager.orm_default`, base directory at `foo/src`, with filter `Foo\Entity`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="d-repo" classname="DoctrineRepoTask" />
        <d-repo em="doctrine.entitymanager.orm_default" filter="Foo\Entity" output="foo/src" />
    </target>
</project>
```

## DoctrineProxyTask

Generate proxies for the EntityManager `doctrine.entitymanager.orm_default`, at directory `foo/cache/proxy`, with filter `Foo\Entity`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="d-proxy" classname="DoctrineProxyTask" />
        <d-proxy em="doctrine.entitymanager.orm_default" filter="Foo\Entity" output="foo/cache/proxy" />
    </target>
</project>
```

## DoctrineUpdateTask

Update database schema for the EntityManager `doctrine.entitymanager.orm_default`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="d-update" classname="DoctrineUpdateTask" />
        <d-update em="doctrine.entitymanager.orm_default" />
    </target>
</project>
```

## DoctrineDropTask

Drop all database tables from the connection of the EntityManager `doctrine.entitymanager.orm_default`.

**This Task differs from Doctrine CLI's behavior. It drops _ALL_ tables, not just those found in the metadata mapping files.**

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="d-drop" classname="DoctrineDropTask" />
        <d-drop em="doctrine.entitymanager.orm_default" />
    </target>
</project>
```

## TwigTask

Load the Twig template `foo/bar`.

```xml
<project>
    <target>
        <includepath classpath="vendor/yalesov/zf2-phing-task/src/task" />
        <taskdef name="twig" classname="TwigTask" />
        <twig file="foo/bar" />
    </target>
</project>
```
