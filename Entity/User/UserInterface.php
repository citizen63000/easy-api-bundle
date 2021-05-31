<?php

namespace EasyApiBundle\Entity\User;

interface UserInterface
{
    public function getId(): ?int;

    public function getUsername(): ?string;

    public function setUsername(?string $username);

    public function getEmail(): ?string;

    public function setEmail(?string $email);
}