# Bonch API
___
API основанное на парсинге сайта sut.ru,
в данный момент позволяет получить данные о группах,
расписании и директорах университета.
---
Полностью работоспособная версия запущена тут:
https://bonch.ssapi.ru/
---
# Структура ошибок:

**Неверный метод**

![1](https://sun9-45.userapi.com/impg/s0pJo-pdPty3FR2xzFgKGF_8FLz_eBqUQOJQWA/HvafAr5tM-o.jpg?size=1140x562&quality=96&sign=aee02c7ad41994ee1bc6b7cd3fc77d73&type=album)
![2](https://sun9-57.userapi.com/impg/Ol_SV1SFfrxtlWZAVLxsq9NcKyw5Xk8rKSTmFw/ImWMXWbmHOA.jpg?size=1166x560&quality=96&sign=bd6208a60c6dd272e8e33afa693416e3&type=album)

---
**Ошибка в параметрах**

![1](https://sun9-24.userapi.com/impg/XIKpexsaOcgYUg950OceQrECRrd5IX7MEpKvAg/k-eWiQ_jQiI.jpg?size=1656x698&quality=96&sign=6d1de50f19f60b0230a3441e11743acf&type=album)

---
**Серверная ошибка**

![1](https://sun9-58.userapi.com/impg/aABCeTf20T8SNd__idSDFhAOmgEmeUExkv9k0w/FHZiDj8hB04.jpg?size=1130x726&quality=96&sign=dfa6a73ef542f20d678f0529a6f14906&type=album)

---
**Другие глобальные ошибки**

![global](https://sun9-31.userapi.com/impg/uzON-XdXr5lV8AkOoZih4xIeYaSxlhyVUBfPng/NUIqfbo-F68.jpg?size=664x236&quality=96&sign=a6cf675678d06e89c644291b0e5afa8a&type=album)

---
# Доступные методы:

**groups.find** — поиск группы по названию

    Параметры: 
        name: string, required — название группы
    Результат:
![Результат](https://sun9-60.userapi.com/impg/BvSP5736yB35PjfTdPbdze5FJ2nQ73iAUx1Z-w/-croi1ZoR6s.jpg?size=1018x464&quality=96&sign=74fdc4e35a19a32503366af9b6e5483d&type=album)

---

**groups.getAll** — получить все группы

    Параметры:
        метод не принимает параметров
    Результат:
![Результат](https://sun9-17.userapi.com/impg/W8jxSBwqWSK2D8VKd2SjXkn47EFp6FCLUs3zsA/nN1BAKxgpJ8.jpg?size=1368x1982&quality=96&sign=895b932df0f0e28e259606378b36d913&type=album)

---

**info.getDirectors** — получить ректоров, проректоров и директоров института.

    Параметры:
        метод не принимает параметров
    Результат:
![Результат](https://sun9-14.userapi.com/impg/AqSq92w06fjvrF2iQb_YLdra0hWl39zmZPvSFQ/0KXvagRJAac.jpg?size=1416x1854&quality=96&sign=f70179767c6e8aaa8ddb16874cb1c1d5&type=album)

---

**schedule.get** — получить расписание группы на определенный день

    Параметры:
        group_id: integer, required — ID группы
        date: date — Дата на которую необходимо получить расписание
    Возможные ошибки:
        №1: Передана невалидная дата, сервер не смог её распознать
    Результат:
![Результат](https://sun9-63.userapi.com/impg/Z4EvI16mI5xVg-obYDJiVgHUL6NFZ-wuTT3E_g/mtcDmXAwz34.jpg?size=1110x1208&quality=96&sign=15edb28be8b4f23f4298da4454ded97a&type=album)

---

**scedule.getWeek** — получает расписание группы на неделю

    Параметры:
        group_id: integer, required — ID группы
        date: date — Любая дата на неделе
    Возможные ошибки:
        №1: Передана невалидная дата, сервер не смог её распознать
    Результат:
![Результат](https://sun9-19.userapi.com/impg/fW_kgBRIJUMz2gcBSBLSg_YnkXGZcCjy4Ws07Q/gX-0VKA0n-8.jpg?size=1236x1788&quality=96&sign=eb275017b45313097c7f69af62b0b44c&type=album)